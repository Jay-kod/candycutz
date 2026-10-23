<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Exceptions\PaymentException;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Events\PaymentConfirmed;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentConfirmer
{
    public function __construct(
        protected PaymentStateMachine $stateMachine,
        protected PricingService $pricingService
    ) {}

    /**
     * Confirms a payment idempotently with row-level locking.
     * Single-writer semantics: only PaymentConfirmer writes final success state.
     *
     * @param  string  $reference  Payment reference or transaction_ref
     * @param  string  $source  'webhook' | 'poll' | 'admin' | 'venue'
     * @param  array<string, mixed>  $payload  Metadata and provider response
     */
    public function confirm(string $reference, string $source, array $payload = []): Payment
    {
        try {
            return DB::transaction(function () use ($reference, $source, $payload) {
                /** @var Payment|null $payment */
                $payment = Payment::where('reference', $reference)
                    ->orWhere('transaction_ref', $reference)
                    ->orWhere('gateway_reference', $reference)
                    ->lockForUpdate()
                    ->first();

                if (! $payment) {
                    Log::error("PaymentConfirmer: Payment not found for reference [{$reference}]");
                    throw PaymentException::notFound("Payment not found for reference [{$reference}]");
                }

                // If already marked successful or terminal success, return idempotently
                $currentStatus = $payment->status instanceof PaymentStatus
                    ? $payment->status
                    : PaymentStatus::tryFrom((string) $payment->status);

                if ($currentStatus->isSuccess()) {
                    Log::info("PaymentConfirmer: Payment [{$reference}] already in success state [{$currentStatus->value}]. Skipping duplicate confirmation.");

                    return $payment;
                }

                $appointment = $payment->appointment;
                if (! $appointment) {
                    Log::error("PaymentConfirmer: Appointment missing for payment [{$reference}]");
                    throw PaymentException::invalidState("Appointment missing for payment [{$reference}]");
                }

                // Verify amount & currency
                $expectedKobo = $this->pricingService->amountDue($appointment);
                $actualKobo = $payment->amount_minor ?: (int) $payment->amount;

                // In payload from Paystack, amount might also be present in kobo
                $payloadKobo = isset($payload['amount']) ? (int) $payload['amount'] : null;
                if ($payloadKobo !== null && $payloadKobo > 0) {
                    $actualKobo = $payloadKobo;
                }

                if ($expectedKobo > 0 && $actualKobo < $expectedKobo) {
                    $flaggedReason = "Amount underpaid: expected {$expectedKobo} kobo, received {$actualKobo} kobo.";
                    Log::warning("PaymentConfirmer: Flagged payment [{$reference}] - {$flaggedReason}");
                    $payment->update([
                        'flagged_reason' => $flaggedReason,
                        'status' => PaymentStatus::failed->value,
                    ]);

                    throw PaymentException::amountMismatch($flaggedReason);
                }

                // Extract provider details from payload
                $providerTxId = (string) ($payload['id'] ?? $payment->provider_transaction_id ?? '');
                $channel = (string) ($payload['channel'] ?? $payment->channel ?? 'card');
                $feesKobo = (int) ($payload['fees'] ?? $payment->fees_minor ?? 0);
                $gatewayResponse = (string) ($payload['gateway_response'] ?? 'Successful');

                // Transition payment state machine to successful
                $this->stateMachine->transition($payment, PaymentStatus::successful, [
                    'amount_minor' => $actualKobo,
                    'provider_transaction_id' => $providerTxId ?: null,
                    'channel' => $channel ?: null,
                    'fees_minor' => $feesKobo,
                    'gateway_response' => $gatewayResponse,
                    'paid_at' => now(),
                ]);

                // Update Appointment
                $oldAppointmentStatus = $appointment->status;
                $appointmentUpdate = [
                    'payment_status' => AppointmentPaymentStatus::paid,
                    'deposit_paid' => true,
                ];

                if ($oldAppointmentStatus === AppointmentStatus::pending) {
                    $appointmentUpdate['status'] = AppointmentStatus::confirmed;
                }

                $appointment->update($appointmentUpdate);

                if ($oldAppointmentStatus === AppointmentStatus::pending) {
                    try {
                        AppointmentStatusHistory::create([
                            'appointment_id' => $appointment->id,
                            'previous_status' => AppointmentStatus::pending->value,
                            'new_status' => AppointmentStatus::confirmed->value,
                            'changed_by_user_id' => $payment->customer_id,
                            'reason' => "Payment confirmed via {$source} ({$payment->provider})",
                            'created_at' => now(),
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning("PaymentConfirmer: Could not record status history: {$e->getMessage()}");
                    }
                }

                // Record transaction ledger entry idempotently
                $eventId = (string) ($providerTxId ?: $reference);
                $idempotencyKey = 'paystack:charge.success:'.$eventId;
                $tx = PaymentTransaction::where('idempotency_key', $idempotencyKey)
                    ->orWhere('gateway_event_id', $eventId)
                    ->first();

                if (! $tx) {
                    PaymentTransaction::create([
                        'payment_id' => $payment->id,
                        'idempotency_key' => $idempotencyKey,
                        'transaction_type' => 'capture',
                        'gateway' => $payment->provider ?: 'paystack',
                        'gateway_event_id' => $eventId,
                        'amount' => $actualKobo,
                        'amount_minor' => $actualKobo,
                        'raw_payload' => $payload,
                        'status' => 'successful',
                        'created_at' => now(),
                    ]);
                }

                // Dispatch domain event
                PaymentConfirmed::dispatch($payment->fresh(), $appointment->fresh());

                Log::info("PaymentConfirmer: Payment [{$reference}] confirmed successfully via [{$source}].");

                return $payment->fresh();
            });
        } catch (PaymentException $e) {
            if ($e->getErrorCode() === 'PAYMENT_AMOUNT_MISMATCH') {
                Payment::where('reference', $reference)
                    ->orWhere('transaction_ref', $reference)
                    ->orWhere('gateway_reference', $reference)
                    ->update([
                        'flagged_reason' => $e->getMessage(),
                        'status' => PaymentStatus::failed->value,
                    ]);
            }

            throw $e;
        }
    }
}
