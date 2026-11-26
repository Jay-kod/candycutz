<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Exceptions\PaymentException;
use App\Domain\Payment\Services\PaymentService;
use App\Domain\Payment\Services\PaymentStateMachine;
use App\Domain\Payment\Services\PricingService;
use App\Domain\Shared\Services\AuditLogger;
use App\Models\NoShowRecord;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InitiateNoShowRefund
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PaymentStateMachine $paymentStateMachine,
        protected PricingService $pricingService
    ) {}

    /**
     * Initiate a refund for a no-show appointment from the Admin Dashboard.
     * Supports both automated gateway refunds (e.g. Paystack) and manual/venue refund initiation.
     */
    public function execute(NoShowRecord $record, User $admin, ?int $amountMinor = null, ?string $reason = null): NoShowRecord
    {
        $record->loadMissing(['appointment.payment', 'appointment.customer']);
        $appointment = $record->appointment;

        if (! $appointment) {
            throw ValidationException::withMessages([
                'record_id' => ['Appointment linked to this no-show record no longer exists.'],
            ]);
        }

        $payment = $appointment->payment;
        if (! $payment || ! $payment->status->isSuccess()) {
            throw ValidationException::withMessages([
                'payment_id' => ['No successful payment exists for this appointment.'],
            ]);
        }

        if ($payment->status === PaymentStatus::refunded || $appointment->payment_status === AppointmentPaymentStatus::refunded) {
            throw ValidationException::withMessages([
                'payment_id' => ['This payment has already been refunded.'],
            ]);
        }

        $maxMinor = $payment->amount_minor ?: ((int) $payment->amount < 100000 ? (int) $payment->amount * 100 : (int) $payment->amount);
        $refundMinor = $amountMinor !== null ? min($maxMinor, max(100, $amountMinor)) : $maxMinor;
        $refundNaira = $refundMinor / 100;
        $refundReason = trim((string) ($reason ?: 'No-show refund initiated by admin'));

        $provider = $payment->provider ?: $payment->payment_method ?: 'paystack';

        if (in_array($provider, ['paystack', 'stripe'], true)) {
            $gateway = $this->paymentService->resolveGateway($provider);
            $gatewayResult = $gateway->refund($payment, $refundMinor, $refundReason);

            if (! $gatewayResult->isSuccessful()) {
                throw PaymentException::providerRejected(
                    $gatewayResult->raw_payload['message'] ?? 'Refund rejected by payment provider.'
                );
            }

            DB::transaction(function () use ($record, $appointment, $payment, $refundMinor, $maxMinor, $refundReason, $admin, $gatewayResult, $provider): void {
                $isFull = $refundMinor >= $maxMinor;
                $targetStatus = $isFull ? PaymentStatus::refunded : PaymentStatus::partially_refunded;

                $this->paymentStateMachine->transition($payment, $targetStatus, [
                    'refunded_minor' => ($payment->refunded_minor ?? 0) + $refundMinor,
                    'gateway_response' => $gatewayResult->raw_payload,
                ]);

                $appointment->update([
                    'payment_status' => AppointmentPaymentStatus::refunded,
                    'deposit_forfeited' => false,
                ]);

                $record->update([
                    'deposit_forfeited' => false,
                    'deposit_forfeited_minor' => 0,
                    'resolution_note' => trim(($record->resolution_note ? $record->resolution_note."\n" : '').'Refunded ₦'.number_format($refundMinor / 100, 2)." via {$provider}: {$refundReason}"),
                ]);

                PaymentTransaction::create([
                    'payment_id' => $payment->id,
                    'transaction_type' => 'refund',
                    'gateway' => $provider,
                    'gateway_event_id' => $gatewayResult->gateway_event_id ?: 'no_show_refund_'.uniqid(),
                    'amount' => $refundMinor,
                    'amount_minor' => $refundMinor,
                    'raw_payload' => ['reason' => $refundReason, 'refunded_by' => $admin->id, 'provider_response' => $gatewayResult->raw_payload],
                    'status' => 'successful',
                    'created_at' => now(),
                ]);

                RefundRequest::updateOrCreate(
                    ['appointment_id' => $appointment->id, 'type' => 'no_show_dispute'],
                    [
                        'payment_id' => $payment->id,
                        'customer_id' => (int) ($appointment->customer_id ?: $admin->id),
                        'reason_category' => 'other',
                        'reason_text' => $refundReason,
                        'refund_amount_minor' => $refundMinor,
                        'original_amount_minor' => $maxMinor,
                        'refund_percentage' => $maxMinor > 0 ? (int) min(100, round(($refundMinor / $maxMinor) * 100)) : 100,
                        'status' => 'completed',
                        'decided_by' => $admin->id,
                        'decided_at' => now(),
                        'decision_note' => $refundReason,
                        'gateway_refund_ref' => $gatewayResult->gateway_event_id ?: null,
                        'refunded_at' => now(),
                    ]
                );
            }, 3);

            try {
                NotificationDispatcher::refundCompleted($appointment, $refundNaira);
            } catch (\Throwable $e) {
                Log::warning("InitiateNoShowRefund completion notification failed: {$e->getMessage()}");
            }
        } else {
            // Manual bank transfer or venue payment: mark refund pending & initiate notification workflow
            DB::transaction(function () use ($record, $appointment, $payment, $refundMinor, $maxMinor, $refundReason, $admin): void {
                $appointment->update([
                    'payment_status' => AppointmentPaymentStatus::refund_pending,
                    'deposit_forfeited' => false,
                ]);

                $record->update([
                    'deposit_forfeited' => false,
                    'deposit_forfeited_minor' => 0,
                    'resolution_note' => trim(($record->resolution_note ? $record->resolution_note."\n" : '').'Manual refund of ₦'.number_format($refundMinor / 100, 2)." initiated: {$refundReason}"),
                ]);

                RefundRequest::updateOrCreate(
                    ['appointment_id' => $appointment->id, 'type' => 'no_show_dispute'],
                    [
                        'payment_id' => $payment->id,
                        'customer_id' => (int) ($appointment->customer_id ?: $admin->id),
                        'reason_category' => 'other',
                        'reason_text' => $refundReason,
                        'refund_amount_minor' => $refundMinor,
                        'original_amount_minor' => $maxMinor,
                        'refund_percentage' => $maxMinor > 0 ? (int) min(100, round(($refundMinor / $maxMinor) * 100)) : 100,
                        'status' => 'processing',
                        'decided_by' => $admin->id,
                        'decided_at' => now(),
                        'decision_note' => $refundReason,
                    ]
                );
            }, 3);

            try {
                NotificationDispatcher::refundInitiated($appointment, $refundNaira, $refundReason);
            } catch (\Throwable $e) {
                Log::warning("InitiateNoShowRefund initiated notification failed: {$e->getMessage()}");
            }
        }

        try {
            AuditLogger::log(
                'no_show.refund_initiated',
                'Payment',
                $admin,
                [],
                ['payment_id' => $payment->id, 'amount_minor' => $refundMinor, 'provider' => $provider, 'reason' => $refundReason],
                NoShowRecord::class,
                $record->id
            );
        } catch (\Throwable) {
            // Non-fatal
        }

        return $record->fresh(['appointment.service', 'appointment.barber.user', 'appointment.payment', 'customer', 'markedBy', 'forfeitedBy', 'resolvedBy']);
    }
}
