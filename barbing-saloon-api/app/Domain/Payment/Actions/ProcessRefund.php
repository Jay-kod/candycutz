<?php

declare(strict_types=1);

namespace App\Domain\Payment\Actions;

use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Services\PaymentService;
use App\Domain\Payment\Services\RefundPolicyEngine;
use App\Domain\Shared\Services\AuditLogger;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProcessRefund
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly RefundPolicyEngine $policyEngine,
    ) {}

    public function execute(RefundRequest $refundRequest, ?User $actor = null): RefundRequest
    {
        /** @var array{mode: string, refund: RefundRequest, payment: Payment|null, provider: string, amount_minor: int, reason: string} $claim */
        $claim = DB::transaction(function () use ($refundRequest): array {
            /** @var RefundRequest $locked */
            $locked = RefundRequest::lockForUpdate()->findOrFail($refundRequest->id);

            if ($locked->status === 'completed') {
                return ['mode' => 'already_done', 'refund' => $locked, 'payment' => null, 'provider' => '', 'amount_minor' => 0, 'reason' => ''];
            }

            // Prevent concurrent workers/requests from initiating duplicate gateway calls
            if ($locked->status === 'processing') {
                return ['mode' => 'already_claimed', 'refund' => $locked, 'payment' => null, 'provider' => '', 'amount_minor' => 0, 'reason' => ''];
            }

            if ($locked->refund_amount_minor <= 0) {
                throw ValidationException::withMessages([
                    'refund_amount' => ['Refund amount must be greater than zero.'],
                ]);
            }

            $appointment = Appointment::lockForUpdate()->findOrFail($locked->appointment_id);
            $payment = $locked->payment_id
                ? Payment::lockForUpdate()->find($locked->payment_id)
                : ($appointment->payment ? Payment::lockForUpdate()->find($appointment->payment->id) : null);

            $provider = strtolower((string) ($payment?->provider ?: $payment?->payment_method ?: $appointment->payment_method ?: 'manual_transfer'));
            $isAutomatedGateway = $payment !== null && in_array($provider, ['paystack', 'stripe'], true);

            $locked->update([
                'payment_id' => $payment?->id,
                'status' => 'processing',
            ]);

            if (! $isAutomatedGateway) {
                $appointment->update([
                    'payment_status' => AppointmentPaymentStatus::refund_pending->value,
                ]);

                AuditLogger::log('refund.manual_queued', 'refunds', $locked, [], [
                    'refund_amount_minor' => $locked->refund_amount_minor,
                    'provider' => $provider,
                ]);

                return [
                    'mode' => 'manual',
                    'refund' => $locked->fresh(['appointment', 'payment', 'customer', 'decider']),
                    'payment' => $payment,
                    'provider' => $provider,
                    'amount_minor' => $locked->refund_amount_minor,
                    'reason' => (string) ($locked->reason_text ?: 'CandyCutz booking refund'),
                ];
            }

            return [
                'mode' => 'gateway',
                'refund' => $locked,
                'payment' => $payment,
                'provider' => $provider,
                'amount_minor' => $locked->refund_amount_minor,
                'reason' => (string) ($locked->reason_text ?: 'CandyCutz booking refund'),
            ];
        }, 3);

        if (in_array($claim['mode'], ['already_done', 'already_claimed'], true)) {
            return $claim['refund']->fresh(['appointment', 'payment', 'customer', 'decider']);
        }

        if ($claim['mode'] === 'manual') {
            $refund = $claim['refund'];
            if ($refund->appointment) {
                NotificationDispatcher::refundInitiated($refund->appointment, round($claim['amount_minor'] / 100, 2), $refund->reason_text);
            }

            return $refund;
        }

        // External HTTP gateway call executed OUTSIDE DB row locks to prevent lock contention under load
        $gatewayResult = null;
        $gatewayError = null;
        try {
            $gateway = $this->paymentService->resolveGateway($claim['provider']);
            $gatewayResult = $gateway->refund($claim['payment'], $claim['amount_minor'], $claim['reason']);
            if (! $gatewayResult->isSuccessful()) {
                $gatewayError = $gatewayResult->error_message ?? 'Gateway refund failed.';
            }
        } catch (\Throwable $e) {
            $gatewayError = $e->getMessage() ?: 'Gateway refund exception.';
        }

        $completedAmountNaira = null;
        $updated = DB::transaction(function () use ($claim, $gatewayResult, $gatewayError, &$completedAmountNaira): RefundRequest {
            /** @var RefundRequest $locked */
            $locked = RefundRequest::lockForUpdate()->findOrFail($claim['refund']->id);
            if ($locked->status === 'completed') {
                return $locked->fresh(['appointment', 'payment', 'customer', 'decider']);
            }

            if ($gatewayError !== null || $gatewayResult === null) {
                $locked->update([
                    'status' => 'failed',
                    'decision_note' => trim(($locked->decision_note ? $locked->decision_note."\n" : '')."Gateway error: {$gatewayError}"),
                ]);

                AuditLogger::log('refund.failed', 'refunds', $locked, [], [
                    'error' => $gatewayError,
                    'provider' => $claim['provider'],
                ]);

                return $locked->fresh(['appointment', 'payment', 'customer', 'decider']);
            }

            $appointment = Appointment::lockForUpdate()->findOrFail($locked->appointment_id);
            $payment = $locked->payment_id ? Payment::lockForUpdate()->find($locked->payment_id) : null;

            $this->finalizeCompletedRefund(
                $locked,
                $appointment,
                $payment,
                $claim['provider'],
                $gatewayResult->gateway_event_id ?: ('RF-'.strtoupper(Str::random(10))),
                $gatewayResult->raw_payload
            );

            $completedAmountNaira = round($locked->refund_amount_minor / 100, 2);

            return $locked->fresh(['appointment', 'payment', 'customer', 'decider']);
        }, 3);

        if ($updated->appointment && $completedAmountNaira !== null) {
            NotificationDispatcher::refundCompleted($updated->appointment, $completedAmountNaira);
        }

        return $updated;
    }

    public function completeManual(
        RefundRequest $refundRequest,
        User $actor,
        ?string $transferReference = null,
        ?string $note = null,
    ): RefundRequest {
        $completedAmountNaira = null;

        $updated = DB::transaction(function () use ($refundRequest, $actor, $transferReference, $note, &$completedAmountNaira): RefundRequest {
            /** @var RefundRequest $locked */
            $locked = RefundRequest::lockForUpdate()->findOrFail($refundRequest->id);

            if (! in_array($locked->status, ['processing', 'approved', 'failed'], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Only processing, approved, or failed refund requests can be marked completed manually.'],
                ]);
            }

            $appointment = Appointment::lockForUpdate()->findOrFail($locked->appointment_id);
            $payment = $locked->payment_id
                ? Payment::lockForUpdate()->find($locked->payment_id)
                : ($appointment->payment ? Payment::lockForUpdate()->find($appointment->payment->id) : null);

            $provider = strtolower((string) ($payment?->provider ?: $payment?->payment_method ?: 'manual_transfer'));
            $ref = $transferReference ?: ('MAN-RF-'.strtoupper(Str::random(8)));

            if ($note !== null && trim($note) !== '') {
                $locked->decision_note = trim(($locked->decision_note ? $locked->decision_note."\n" : '').trim($note));
            }
            $locked->decided_by = $locked->decided_by ?: $actor->id;
            $locked->decided_at = $locked->decided_at ?: now();
            $locked->save();

            $this->finalizeCompletedRefund($locked, $appointment, $payment, $provider, $ref, [
                'manual_completed_by' => $actor->id,
                'transfer_reference' => $ref,
                'note' => $note,
            ]);

            $completedAmountNaira = round($locked->refund_amount_minor / 100, 2);

            return $locked->fresh(['appointment', 'payment', 'customer', 'decider']);
        }, 3);

        if ($updated->appointment && $completedAmountNaira !== null) {
            NotificationDispatcher::refundCompleted($updated->appointment, $completedAmountNaira);
        }

        return $updated;
    }

    /**
     * @param  array<string, mixed>  $rawPayload
     */
    private function finalizeCompletedRefund(
        RefundRequest $refundRequest,
        Appointment $appointment,
        ?Payment $payment,
        string $provider,
        string $refundRef,
        array $rawPayload,
    ): void {
        $now = now();

        $refundRequest->update([
            'payment_id' => $payment?->id,
            'status' => 'completed',
            'gateway_refund_ref' => $refundRef,
            'refunded_at' => $now,
        ]);

        if ($payment) {
            $paidMinor = max($this->policyEngine->resolvePaidMinor($appointment, $payment), $refundRequest->original_amount_minor);
            $newRefundedMinor = (int) $payment->refunded_minor + $refundRequest->refund_amount_minor;
            $isFull = $paidMinor > 0 && $newRefundedMinor >= $paidMinor;

            $payment->update([
                'refunded_minor' => $newRefundedMinor,
                'refund_reason' => $refundRequest->reason_text ?: $refundRequest->reason_category,
                'refunded_at' => $now,
                'status' => $isFull ? PaymentStatus::refunded->value : PaymentStatus::partially_refunded->value,
            ]);

            PaymentTransaction::create([
                'payment_id' => $payment->id,
                'transaction_type' => 'refund',
                'gateway' => $provider,
                'gateway_event_id' => $refundRef,
                'amount' => (int) round($refundRequest->refund_amount_minor / 100),
                'amount_minor' => $refundRequest->refund_amount_minor,
                'idempotency_key' => "refund-req:{$refundRequest->id}:{$refundRef}",
                'raw_payload' => $rawPayload,
                'status' => 'completed',
                'created_at' => $now,
            ]);
        }

        $appointment->update([
            'payment_status' => AppointmentPaymentStatus::refunded->value,
        ]);

        AuditLogger::log('refund.completed', 'refunds', $refundRequest, [], [
            'refund_amount_minor' => $refundRequest->refund_amount_minor,
            'gateway_refund_ref' => $refundRef,
            'provider' => $provider,
        ]);
    }
}
