<?php

declare(strict_types=1);

namespace App\Domain\Payment\Gateways;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\DataObjects\GatewayCheckout;
use App\Domain\Payment\DataObjects\GatewayResult;
use App\Domain\Payment\DataObjects\WebhookEvent;
use App\Models\Payment;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\StripeObject;
use Stripe\Webhook;

class StripeGateway implements PaymentGateway
{
    protected string $secretKey;

    protected string $webhookSecret;

    public function __construct()
    {
        $this->secretKey = config('payments.gateways.stripe.secret_key', '');
        $this->webhookSecret = config('payments.gateways.stripe.webhook_secret', '');
        Stripe::setApiKey($this->secretKey);
    }

    public function initiate(Payment $payment): GatewayCheckout
    {
        $amountKobo = (int) $payment->amount;
        $reference = $payment->transaction_ref ?? 'STRIPE_'.strtoupper(uniqid());

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd', // Stripe doesn't support NGN easily without special setup
                    'product_data' => [
                        'name' => 'Appointment for '.($payment->appointment?->service?->name ?? 'Service'),
                    ],
                    'unit_amount' => $amountKobo,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url('/api/v1/payments/stripe/success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => url('/api/v1/payments/stripe/cancel'),
            'client_reference_id' => $reference,
            'metadata' => [
                'appointment_id' => $payment->appointment_id,
                'customer_id' => $payment->customer_id,
            ],
        ]);

        return new GatewayCheckout(
            reference: $reference,
            gateway: 'stripe',
            authorization_url: $session->url,
            access_code: $session->id,
            amount_kobo: $amountKobo,
        );
    }

    public function verify(string $reference): GatewayResult
    {
        // Reference is the session ID in this context if we want to verify via Session.
        $session = Session::retrieve($reference);

        $status = match ($session->payment_status) {
            'paid' => 'success',
            'unpaid' => 'pending',
            default => 'failed',
        };

        return new GatewayResult(
            reference: $session->client_reference_id ?? $reference,
            status: $status,
            amount_kobo: $session->amount_total,
            gateway: 'stripe',
            gateway_event_id: $session->payment_intent,
            raw_payload: $session->toArray(),
        );
    }

    /**
     * @param  array<string, mixed>  $headers
     */
    public function handleWebhook(string $rawPayload, array $headers): WebhookEvent
    {
        $signatureHeader = $headers['stripe-signature'] ?? null;
        if (is_array($signatureHeader)) {
            $signatureHeader = $signatureHeader[0] ?? '';
        }

        try {
            $event = Webhook::constructEvent(
                $rawPayload, $signatureHeader, $this->webhookSecret
            );
        } catch (SignatureVerificationException $e) {
            throw new RuntimeException('Invalid Stripe webhook signature.', 0, $e);
        }

        /** @var StripeObject $data */
        $data = $event->data;
        /** @var Session $session */
        $session = $data->object;
        $reference = $session->client_reference_id ?? '';
        $amount = $session->amount_total ?? 0;
        $eventId = $event->id;

        $status = match ($event->type) {
            'checkout.session.completed' => 'success',
            'checkout.session.async_payment_failed' => 'failed',
            default => 'pending',
        };

        return new WebhookEvent(
            event_type: $event->type,
            reference: $reference,
            gateway_event_id: $eventId,
            amount_kobo: $amount,
            status: $status,
            gateway: 'stripe',
            raw_payload: $event->toArray(),
        );
    }
}
