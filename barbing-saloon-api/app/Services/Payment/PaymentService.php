<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Exception;
use Illuminate\Support\Facades\DB;
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
            'customer_id' => $appointment->customer_id,
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
     * Confirm verified payment webhook.
     */
    public function confirmPaymentFromWebhook(string $paymentIntentId, string $eventId, array $rawPayload): Payment
    {
        return DB::transaction(function () use ($paymentIntentId, $eventId, $rawPayload) {
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->firstOrFail();

            if ($payment->status === 'successful') {
                return $payment; // Already settled
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
            $appointment->update([
                'status' => 'confirmed',
                'deposit_paid' => true,
            ]);

            return $payment;
        });
    }
}
