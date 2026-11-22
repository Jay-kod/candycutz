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
use Carbon\Carbon;
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
                $payment = Payment::where(function ($q) use ($reference) {
                    $q->where('reference', $reference)
                        ->orWhere('transaction_ref', $reference)
                        ->orWhere('gateway_reference', $reference);
                })
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

                $appointment = Appointment::whereKey($payment->appointment_id)->lockForUpdate()->first();
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
                $oldStatusValue = $oldAppointmentStatus instanceof AppointmentStatus
                    ? $oldAppointmentStatus->value
                    : (string) $oldAppointmentStatus;

                $appointmentUpdate = [
                    'payment_status' => AppointmentPaymentStatus::paid,
                    'deposit_paid' => true,
                ];

                $shouldSendConfirmationEmail = false;

                if (in_array($oldStatusValue, ['pending', 'draft'], true)) {
                    $appointmentUpdate['status'] = AppointmentStatus::confirmed;
                    $appointmentUpdate['draft_expires_at'] = null;
                    $shouldSendConfirmationEmail = true;
                } elseif ($oldStatusValue === 'cancelled' && str_contains(strtolower((string) $appointment->cancellation_reason), 'expire')) {
                    // Late payment arrived after draft hold expired. Check if slot is still free.
                    $barberId = $appointment->barber_id;
                    $date = Carbon::parse($appointment->appointment_date)->toDateString();
                    $startTime = substr(trim((string) $appointment->appointment_time), 0, 5);
                    $endTime = $appointment->end_time
                        ? substr(trim((string) $appointment->end_time), 0, 5)
                        : Carbon::parse("{$date} {$startTime}")->addMinutes($appointment->total_duration_minutes ?: 30)->format('H:i');

                    $slotTaken = false;
                    if ($barberId) {
                        $slotTaken = Appointment::where('barber_id', $barberId)
                            ->where('id', '!=', $appointment->id)
                            ->where('appointment_date', $date)
                            ->whereNotIn('status', [AppointmentStatus::cancelled->value, 'cancelled'])
                            ->where(function ($q) {
                                $q->whereNotIn('status', [AppointmentStatus::draft->value, 'draft'])
                                    ->orWhere(function ($draftQuery) {
                                        $draftQuery->whereIn('status', [AppointmentStatus::draft->value, 'draft'])
                                            ->where(function ($tq) {
                                                $tq->whereNull('draft_expires_at')
                                                    ->orWhere('draft_expires_at', '>', now());
                                            });
                                    });
                            })
                            ->where(function ($query) use ($startTime, $endTime) {
                                $query->where('appointment_time', '<', $endTime)
                                    ->where(function ($q) use ($startTime) {
                                        $q->where('end_time', '>', $startTime)
                                            ->orWhereNull('end_time');
                                    });
                            })
                            ->exists();
                    }

                    if (! $slotTaken) {
                        $appointmentUpdate['status'] = AppointmentStatus::confirmed;
                        $appointmentUpdate['draft_expires_at'] = null;
                        $appointmentUpdate['cancellation_reason'] = null;
                        $shouldSendConfirmationEmail = true;
                    } else {
                        $appointmentUpdate['notes'] = trim(($appointment->notes ?? '')."\n[CRITICAL: Payment received after draft hold expired, but slot was re-booked. Staff must re-assign.]");
                        Log::alert("PaymentConfirmer: Payment received for expired draft appointment #{$appointment->id}, but slot ({$date} {$startTime}) was already taken.");
                    }
                }

                $appointment->update($appointmentUpdate);

                if ($shouldSendConfirmationEmail) {
                    try {
                        AppointmentStatusHistory::create([
                            'appointment_id' => $appointment->id,
                            'previous_status' => $oldStatusValue,
                            'new_status' => AppointmentStatus::confirmed->value,
                            'changed_by_user_id' => $payment->customer_id,
                            'reason' => "Payment confirmed via {$source} ({$payment->provider})",
                            'created_at' => now(),
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning("PaymentConfirmer: Could not record status history: {$e->getMessage()}");
                    }

                    // Dispatch luxury booking confirmation email upon payment completion
                    try {
                        \App\Jobs\SendBookingConfirmation::dispatch($appointment);
                    } catch (\Throwable $e) {
                        Log::warning("PaymentConfirmer: Could not dispatch SendBookingConfirmation: {$e->getMessage()}");
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
