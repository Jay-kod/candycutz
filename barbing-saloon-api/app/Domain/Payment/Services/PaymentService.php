<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Gateways\ManualTransferGateway;
use App\Domain\Payment\Gateways\PaystackGateway;
use App\Domain\Payment\Gateways\StripeGateway;
use App\Events\PaymentFailed;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        protected PaymentGateway $gateway,
        protected PricingService $pricingService
    ) {}

    public function resolveGateway(string $paymentMethod): PaymentGateway
    {
        return match ($paymentMethod) {
            'paystack' => app(PaystackGateway::class),
            'manual_transfer' => app(ManualTransferGateway::class),
            'stripe' => app(StripeGateway::class),
            default => $this->gateway,
        };
    }

    /**
     * Initialize payment intent for an appointment.
     *
     * @return array<string, mixed>
     */
    public function initializePayment(Appointment $appointment, string $paymentMethod = 'paystack', string $client = 'web'): array
    {
        $amount = $this->pricingService->amountDue($appointment);
        $currency = 'NGN';
        $initialStatus = $paymentMethod === 'manual_transfer' ? 'awaiting_transfer' : 'pending';
        $provider = $paymentMethod === 'manual_transfer' ? 'manual_transfer' : 'paystack';
        $reference = 'CC-'.strtoupper(Str::random(12));

        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $appointment->customer_id ?? 1,
            'amount' => $amount,
            'amount_minor' => $amount,
            'currency' => $currency,
            'status' => $initialStatus,
            'provider' => $provider,
            'payment_method' => $paymentMethod,
            'reference' => $reference,
            'transaction_ref' => $reference,
            'client' => $client,
            'customer_email' => $appointment->customer?->email ?? $appointment->client_email,
            'expires_at' => now()->addMinutes(30),
        ]);

        $gateway = $this->resolveGateway($paymentMethod);
        $checkout = $gateway->initiate($payment);

        $payment->update([
            'gateway_reference' => $checkout->reference,
            'reference' => $checkout->reference ?: $reference,
            'access_code' => $checkout->access_code,
            'authorization_url' => $checkout->authorization_url,
        ]);

        return array_merge($checkout->toArray(), [
            'payment_id' => $payment->id,
            'transaction_ref' => $payment->transaction_ref,
            'reference' => $payment->reference,
            'access_code' => $payment->access_code,
            'authorization_url' => $payment->authorization_url,
        ]);
    }

    /**
     * Confirm verified payment webhook with strict idempotency protection.
     *
     * @param  array<string, mixed>  $rawPayload
     */
    public function confirmPaymentFromWebhook(string $gatewayReference, string $eventId, array $rawPayload): Payment
    {
        $payloadData = $rawPayload['data'] ?? $rawPayload;
        $payloadData['id'] = $eventId;

        return app(PaymentConfirmer::class)->confirm($gatewayReference, 'webhook', $payloadData);
    }

    /**
     * Record failed payment attempt from webhook.
     *
     * @param  array<string, mixed>  $rawPayload
     */
    public function recordFailedPaymentFromWebhook(string $gatewayReference, string $eventId, array $rawPayload, ?string $errorMessage = null): ?Payment
    {
        return DB::transaction(function () use ($gatewayReference, $eventId, $rawPayload, $errorMessage) {
            $existingTxn = PaymentTransaction::where('gateway_event_id', $eventId)->first();
            if ($existingTxn) {
                return $existingTxn->payment;
            }

            $payment = Payment::where('gateway_reference', $gatewayReference)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                ]);

                PaymentTransaction::create([
                    'payment_id' => $payment->id,
                    'transaction_type' => 'void',
                    'gateway' => config('payments.default'),
                    'gateway_event_id' => $eventId,
                    'amount' => $payment->amount,
                    'raw_payload' => $rawPayload,
                    'status' => 'failed',
                    'created_at' => now(),
                ]);

                PaymentFailed::dispatch($payment, $errorMessage ?? 'Payment failed via webhook');
            }

            return $payment;
        });
    }
}
