<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Core\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        protected StripePaymentProvider $stripeProvider
    ) {}

    /**
     * Initialize payment intent for an appointment.
     */
    public function initializePayment(Appointment $appointment, string $paymentMethod = 'stripe'): array
    {
        $amount = (float) $appointment->grand_total;
        $currency = 'NGN';

        $intentData = $this->stripeProvider->createPaymentIntent($appointment, $amount, $currency);

        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $appointment->customer_id ?? 1,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'payment_method' => $paymentMethod,
            'stripe_payment_intent_id' => $intentData['payment_intent_id'],
            'transaction_ref' => 'TXN-' . strtoupper(Str::random(12)),
        ]);

        return array_merge($intentData, [
            'payment_id' => $payment->id,
            'transaction_ref' => $payment->transaction_ref,
        ]);
    }

    /**
     * Confirm verified payment webhook with strict idempotency protection.
     */
    public function confirmPaymentFromWebhook(string $paymentIntentId, string $eventId, array $rawPayload): Payment
    {
        return DB::transaction(function () use ($paymentIntentId, $eventId, $rawPayload) {
            // Idempotency check: Has this exact event already been processed?
            $existingTxn = PaymentTransaction::where('gateway_event_id', $eventId)->first();
            if ($existingTxn) {
                Log::info("Idempotent webhook skipped for event: {$eventId}");
                return $existingTxn->payment;
            }

            // Find payment by stripe payment intent ID
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();

            // If payment record wasn't created yet, check appointment metadata in payload
            if (!$payment) {
                $object = $rawPayload['data']['object'] ?? $rawPayload;
                $bookingRef = $object['metadata']['booking_reference'] ?? null;
                $appointmentId = $object['metadata']['appointment_id'] ?? null;

                $appointment = null;
                if ($bookingRef) {
                    $appointment = Appointment::where('booking_reference', $bookingRef)->first();
                } elseif ($appointmentId) {
                    $appointment = Appointment::find($appointmentId);
                }

                if ($appointment) {
                    $amount = isset($object['amount_received']) 
                        ? ((float) $object['amount_received']) / 100 
                        : (isset($object['amount']) ? ((float) $object['amount']) / 100 : (float) $appointment->grand_total);

                    $payment = Payment::create([
                        'appointment_id' => $appointment->id,
                        'customer_id' => $appointment->customer_id ?? 1,
                        'amount' => $amount ?: (float) $appointment->grand_total,
                        'currency' => strtoupper((string) ($object['currency'] ?? 'NGN')),
                        'status' => 'pending',
                        'payment_method' => 'stripe',
                        'stripe_payment_intent_id' => $paymentIntentId,
                        'transaction_ref' => 'TXN-' . strtoupper(Str::random(12)),
                    ]);
                }
            }

            if (!$payment) {
                throw new Exception("Payment record for Intent {$paymentIntentId} not found.");
            }

            if ($payment->status === 'successful') {
                // Ensure transaction log entry exists for this event
                PaymentTransaction::firstOrCreate(
                    ['gateway_event_id' => $eventId],
                    [
                        'payment_id' => $payment->id,
                        'transaction_type' => 'capture',
                        'gateway' => 'stripe',
                        'amount' => $payment->amount,
                        'raw_payload' => $rawPayload,
                        'status' => 'success',
                        'created_at' => now(),
                    ]
                );
                return $payment;
            }

            $payment->update([
                'status' => 'successful',
            ]);

            PaymentTransaction::create([
                'payment_id' => $payment->id,
                'transaction_type' => 'capture',
                'gateway' => 'stripe',
                'gateway_event_id' => $eventId,
                'amount' => $payment->amount,
                'raw_payload' => $rawPayload,
                'status' => 'success',
                'created_at' => now(),
            ]);

            // Update associated appointment to confirmed
            $appointment = $payment->appointment;
            if ($appointment) {
                $appointment->update([
                    'status' => AppointmentStatus::confirmed->value,
                    'deposit_paid' => true,
                ]);

                try {
                    AppointmentStatusHistory::create([
                        'appointment_id' => $appointment->id,
                        'previous_status' => AppointmentStatus::pending->value,
                        'new_status' => AppointmentStatus::confirmed->value,
                        'changed_by_user_id' => $payment->customer_id ?? 1,
                        'reason' => "Stripe payment captured (Event {$eventId})",
                        'created_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("Could not record status history for payment: " . $e->getMessage());
                }
            }

            return $payment;
        });
    }

    /**
     * Record failed payment attempt from webhook.
     */
    public function recordFailedPaymentFromWebhook(string $paymentIntentId, string $eventId, array $rawPayload, ?string $errorMessage = null): ?Payment
    {
        return DB::transaction(function () use ($paymentIntentId, $eventId, $rawPayload, $errorMessage) {
            $existingTxn = PaymentTransaction::where('gateway_event_id', $eventId)->first();
            if ($existingTxn) {
                return $existingTxn->payment;
            }

            $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                ]);

                PaymentTransaction::create([
                    'payment_id' => $payment->id,
                    'transaction_type' => 'void',
                    'gateway' => 'stripe',
                    'gateway_event_id' => $eventId,
                    'amount' => $payment->amount,
                    'raw_payload' => $rawPayload,
                    'status' => 'failed',
                    'created_at' => now(),
                ]);
            }

            return $payment;
        });
    }
}
