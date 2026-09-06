<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Appointment;
use App\Models\Payment;
use Exception;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;

class StripePaymentProvider
{
    public function __construct()
    {
        $secret = config('services.stripe.secret') ?: env('STRIPE_SECRET');
        if ($secret) {
            Stripe::setApiKey($secret);
        }
    }

    /**
     * Create a Stripe PaymentIntent for an appointment.
     */
    public function createPaymentIntent(Appointment $appointment, float $amount, string $currency = 'NGN'): array
    {
        $secret = config('services.stripe.secret') ?: env('STRIPE_SECRET');
        
        // If Stripe keys are not yet configured in local test mode, return a safe mock intent
        if (!$secret || str_starts_with($secret, 'sk_test_placeholder')) {
            $mockIntentId = 'pi_mock_' . bin2hex(random_bytes(12));
            $mockClientSecret = $mockIntentId . '_secret_' . bin2hex(random_bytes(10));
            return [
                'payment_intent_id' => $mockIntentId,
                'client_secret' => $mockClientSecret,
                'amount' => $amount,
                'currency' => $currency,
                'is_mock' => true,
            ];
        }

        // Stripe amounts are in cents/kobo (multiply by 100)
        $amountInMinorUnits = (int) round($amount * 100);

        $intent = PaymentIntent::create([
            'amount' => $amountInMinorUnits,
            'currency' => strtolower($currency),
            'metadata' => [
                'appointment_id' => (string) $appointment->id,
                'booking_reference' => (string) $appointment->booking_reference,
                'customer_id' => (string) $appointment->customer_id,
            ],
            'description' => "CandyCutz Grooming — Booking #{$appointment->booking_reference}",
        ]);

        return [
            'payment_intent_id' => $intent->id,
            'client_secret' => $intent->client_secret,
            'amount' => $amount,
            'currency' => $currency,
            'is_mock' => false,
        ];
    }

    /**
     * Issue an authoritative refund through Stripe.
     */
    public function refund(Payment $payment, ?float $amount = null, ?string $reason = null): array
    {
        $secret = config('services.stripe.secret') ?: env('STRIPE_SECRET');
        
        if (!$secret || str_starts_with($secret, 'sk_test_placeholder') || str_starts_with($payment->stripe_payment_intent_id, 'pi_mock_')) {
            return [
                'refund_id' => 're_mock_' . bin2hex(random_bytes(10)),
                'status' => 'succeeded',
                'amount' => $amount ?: $payment->amount,
            ];
        }

        $params = ['payment_intent' => $payment->stripe_payment_intent_id];
        if ($amount) {
            $params['amount'] = (int) round($amount * 100);
        }

        $refund = Refund::create($params);

        return [
            'refund_id' => $refund->id,
            'status' => $refund->status,
            'amount' => $refund->amount / 100,
        ];
    }
}
