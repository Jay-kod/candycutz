<?php

declare(strict_types=1);

namespace App\Domain\Payment\Actions;

use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Payment\Services\RefundPolicyEngine;
use App\Domain\Shared\Services\AuditLogger;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DecideRefundRequest
{
    public function __construct(
        private readonly ProcessRefund $processRefund,
        private readonly RefundPolicyEngine $policyEngine,
    ) {}

    public function execute(
        RefundRequest $refundRequest,
        User $admin,
        string $decision,
        ?string $note = null,
        ?int $overrideAmountMinor = null,
    ): RefundRequest {
        if (! in_array($decision, ['approve', 'reject'], true)) {
            throw ValidationException::withMessages([
                'decision' => ['Decision must be either approve or reject.'],
            ]);
        }

        $outcome = DB::transaction(function () use ($refundRequest, $admin, $decision, $note, $overrideAmountMinor): RefundRequest {
            /** @var RefundRequest $locked */
            $locked = RefundRequest::with(['appointment.payment', 'payment', 'customer'])
                ->lockForUpdate()
                ->findOrFail($refundRequest->id);

            if (! in_array($locked->status, ['pending', 'failed'], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Only pending or failed refund requests can be decided.'],
                ]);
            }

            if ($decision === 'reject') {
                $locked->update([
                    'status' => 'rejected',
                    'decided_by' => $admin->id,
                    'decided_at' => now(),
                    'decision_note' => $note,
                ]);

                if ($locked->appointment && $locked->appointment->payment_status === AppointmentPaymentStatus::refund_pending) {
                    $paidMinor = $this->policyEngine->resolvePaidMinor($locked->appointment, $locked->payment);
                    if ($paidMinor > 0) {
                        $locked->appointment->update([
                            'payment_status' => AppointmentPaymentStatus::paid->value,
                        ]);
                    }
                }

                AuditLogger::log('refund.rejected', 'refunds', $locked, [], [
                    'decision_note' => $note,
                ]);

                return $locked->fresh(['appointment', 'payment', 'customer', 'decider']);
            }

            $approvedMinor = $overrideAmountMinor !== null
                ? min($overrideAmountMinor, max($locked->original_amount_minor, $locked->refund_amount_minor))
                : $locked->refund_amount_minor;

            if ($approvedMinor <= 0) {
                throw ValidationException::withMessages([
                    'refund_amount_minor' => ['Approved refund amount must be greater than zero.'],
                ]);
            }

            $percentage = $locked->original_amount_minor > 0
                ? (int) min(100, round(($approvedMinor / $locked->original_amount_minor) * 100))
                : $locked->refund_percentage;

            $locked->update([
                'refund_amount_minor' => $approvedMinor,
                'refund_percentage' => $percentage,
                'status' => 'approved',
                'decided_by' => $admin->id,
                'decided_at' => now(),
                'decision_note' => $note,
            ]);

            AuditLogger::log('refund.approved', 'refunds', $locked, [], [
                'approved_amount_minor' => $approvedMinor,
                'percentage' => $percentage,
                'decision_note' => $note,
            ]);

            return $locked->fresh(['appointment', 'payment', 'customer', 'decider']);
        }, 3);

        if ($outcome->status === 'rejected') {
            NotificationDispatcher::refundRejected($outcome);

            return $outcome;
        }

        NotificationDispatcher::refundApproved($outcome);

        return $this->processRefund->execute($outcome, $admin);
    }
}
