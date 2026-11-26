<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\Services\BookingService;
use App\Domain\Payment\Actions\RequestRefund;
use App\Domain\Payment\DataObjects\CancellationResult;
use App\Domain\Payment\Services\RefundPolicyEngine;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CancelAppointment
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly RefundPolicyEngine $policyEngine,
        private readonly RequestRefund $requestRefund,
    ) {}

    public function execute(
        Appointment $appointment,
        ?User $actor = null,
        string $reason = 'Customer requested cancellation',
        string $reasonCategory = 'changed_mind',
        bool $requestCompassionate = false,
        ?UploadedFile $evidence = null,
    ): CancellationResult {
        $resolvedActor = $actor ?? $appointment->customer;
        $isCompassionate = $requestCompassionate || in_array($reasonCategory, ['illness', 'emergency'], true);

        $eligibility = DB::transaction(function () use ($appointment, $resolvedActor, $reason, $reasonCategory, $isCompassionate) {
            /** @var Appointment $locked */
            $locked = Appointment::with('payment')->whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            $computed = $this->policyEngine->evaluate($locked, $reasonCategory, $isCompassionate);

            if ($resolvedActor) {
                $this->bookingService->transitionStatus(
                    $locked,
                    AppointmentStatus::cancelled->value,
                    $resolvedActor,
                    $reason
                );
            } else {
                $locked->update([
                    'status' => AppointmentStatus::cancelled->value,
                    'cancellation_reason' => $reason,
                ]);
            }

            return $computed;
        }, 3);

        $refundRequest = null;
        if ($resolvedActor && $eligibility->eligible && $eligibility->refundAmountMinor > 0) {
            $existingActive = RefundRequest::where('appointment_id', $appointment->id)
                ->whereIn('status', ['pending', 'approved', 'processing', 'completed'])
                ->first();

            $refundRequest = $existingActive ?: $this->requestRefund->execute(
                appointment: $appointment,
                actor: $resolvedActor,
                reasonCategory: $reasonCategory,
                reasonText: $reason,
                isCompassionate: $isCompassionate,
                evidence: $evidence,
            );
        }

        $fresh = $appointment->fresh()->load(['service.category', 'barber.user', 'serviceZone', 'payment', 'latestRefundRequest']);

        return new CancellationResult(
            appointment: $fresh,
            eligibility: $eligibility,
            refundRequest: $refundRequest,
        );
    }
}
