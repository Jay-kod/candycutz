<?php

declare(strict_types=1);

namespace App\Domain\Payment\Gateways;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\DataObjects\GatewayCheckout;
use App\Domain\Payment\DataObjects\GatewayResult;
use App\Domain\Payment\DataObjects\WebhookEvent;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaystackGateway implements PaymentGateway
{
    protected string $secretKey;

    protected string $publicKey;

    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('payments.gateways.paystack.secret_key', '');
        $this->publicKey = config('payments.gateways.paystack.public_key', '');
        $this->baseUrl = config('payments.gateways.paystack.base_url', 'https://api.paystack.co');
    }

    public function initiate(Payment $payment): GatewayCheckout
    {
        // Paystack uses kobo
        $amountKobo = (int) $payment->amount;
        $email = $payment->customer->email ?? 'customer@candycutz.com';
        $reference = $payment->transaction_ref ?? 'TXN_'.strtoupper(uniqid());

        // In mock mode (no keys), return a mock checkout url
        if (empty($this->secretKey) || str_starts_with($this->secretKey, 'sk_test_placeholder')) {
            Log::info("Paystack Mock: Initiating payment for Ref {$reference}");

            return new GatewayCheckout(
                reference: $reference,
                gateway: 'paystack',
                authorization_url: url("/mock-paystack/checkout/{$reference}"),
                access_code: 'mock_access_code',
                amount_kobo: $amountKobo,
            );
        }

        $callbackUrl = config('payments.gateways.paystack.callback_url')
            ?: url('/api/v1/payments/callback');

        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'amount' => $amountKobo,
                'email' => $email,
                'reference' => $reference,
                'callback_url' => $callbackUrl,
                'metadata' => [
                    'appointment_id' => $payment->appointment_id,
                    'customer_id' => $payment->customer_id,
                ],
            ]);

        if ($response->failed() || ! ($response->json('status') ?? false)) {
            Log::error('Paystack initialize failed: '.$response->body());
            throw new RuntimeException('Could not initialize Paystack transaction: '.$response->json('message', 'Unknown error'));
        }

        $data = $response->json('data');

        return new GatewayCheckout(
            reference: $data['reference'] ?? $reference,
            gateway: 'paystack',
            authorization_url: $data['authorization_url'],
            access_code: $data['access_code'],
            amount_kobo: $amountKobo,
        );
    }

    public function verify(string $reference): GatewayResult
    {
        if (empty($this->secretKey) || str_starts_with($this->secretKey, 'sk_test_placeholder')) {
            return new GatewayResult(
                reference: $reference,
                status: 'success',
                amount_kobo: 0,
                gateway: 'paystack',
            );
        }

        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transaction/verify/".rawurlencode($reference));

        if ($response->failed() || ! ($response->json('status') ?? false)) {
            Log::error('Paystack verify failed: '.$response->body());
            throw new RuntimeException('Could not verify Paystack transaction: '.$response->json('message', 'Unknown error'));
        }

        $data = $response->json('data');
        $status = match ($data['status'] ?? 'pending') {
            'success' => 'success',
            'failed' => 'failed',
            'abandoned' => 'failed',
            default => 'pending',
        };

        return new GatewayResult(
            reference: $data['reference'],
            status: $status,
            amount_kobo: (int) ($data['amount'] ?? 0),
            gateway: 'paystack',
            gateway_event_id: (string) ($data['id'] ?? ''),
            raw_payload: $data,
        );
    }

    public function handleWebhook(string $rawPayload, array $headers): WebhookEvent
    {
        $signatureHeader = $headers['x-paystack-signature'] ?? null;

        if (is_array($signatureHeader)) {
            $signatureHeader = $signatureHeader[0] ?? '';
        }

        if (! $signatureHeader) {
            throw new RuntimeException('Missing Paystack signature header.');
        }

        if (empty($this->secretKey) || str_starts_with($this->secretKey, 'sk_test_placeholder')) {
            throw new RuntimeException('Paystack webhook verification unavailable without configured keys.');
        }

        $expectedSignature = hash_hmac('sha512', $rawPayload, $this->secretKey);

        if (! hash_equals($expectedSignature, $signatureHeader)) {
            throw new RuntimeException('Invalid Paystack webhook signature.');
        }

        $payload = json_decode($rawPayload, true);
        if (! $payload) {
            throw new RuntimeException('Invalid JSON payload in webhook.');
        }

        $event = $payload['event'] ?? 'unknown';
        $data = $payload['data'] ?? [];
        $reference = $data['reference'] ?? '';
        $amount = (int) ($data['amount'] ?? 0);
        $eventId = (string) ($data['id'] ?? '');

        // Map Paystack event to standardized status
        $status = match ($event) {
            'charge.success' => 'success',
            'charge.failed' => 'failed',
            default => 'pending', // e.g. charge.dispute.create
        };

        return new WebhookEvent(
            event_type: $event,
            reference: $reference,
            gateway_event_id: $eventId,
            amount_kobo: $amount,
            status: $status,
            gateway: 'paystack',
            raw_payload: $payload,
        );
    }
}
