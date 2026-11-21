<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Services\PaymentConfirmer;
use App\Domain\Payment\Services\PaymentService;
use App\Models\WebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookApiController
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PaymentConfirmer $paymentConfirmer,
        protected PaymentGateway $gateway
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $rawPayload = (string) $request->getContent();
        $headers = $request->headers->all();

        // 1. Signature Verification via gateway
        try {
            $event = $this->gateway->handleWebhook($rawPayload, $headers);
        } catch (\Throwable $e) {
            Log::warning('Webhook Verification Failed: '.$e->getMessage());

            try {
                WebhookEvent::create([
                    'provider' => 'paystack',
                    'event' => 'unknown',
                    'provider_event_key' => 'invalid_sig_'.uniqid(),
                    'signature_valid' => false,
                    'payload' => json_decode($rawPayload, true) ?: ['raw' => $rawPayload],
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'request_id' => $request->header('x-request-id'),
                    'received_at' => now(),
                ]);
            } catch (\Throwable) {
                // Ignore DB logging failure on invalid payload
            }

            return response()->json(['error' => 'Invalid signature or payload: '.$e->getMessage()], 400);
        }

        $providerEventKey = "paystack:{$event->event_type}:".($event->gateway_event_id ?: $event->reference);
        Log::info("Webhook Received: {$event->event_type} [Event: {$event->gateway_event_id}, Key: {$providerEventKey}]");

        // 2. Webhook Event Idempotency Check
        $webhookEvent = WebhookEvent::firstOrCreate(
            ['provider_event_key' => $providerEventKey],
            [
                'provider' => 'paystack',
                'event' => $event->event_type,
                'signature_valid' => true,
                'payload' => $event->raw_payload,
                'status' => 'received',
                'request_id' => $request->header('x-request-id'),
                'received_at' => now(),
            ]
        );

        if ($webhookEvent->wasRecentlyCreated === false && $webhookEvent->isProcessed()) {
            Log::info("Webhook [{$providerEventKey}] already processed. Responding 200 immediately.");

            return response()->json([
                'received' => true,
                'duplicate' => true,
                'event_id' => $event->gateway_event_id,
                'type' => $event->event_type,
            ]);
        }

        // 3. Process event
        try {
            if ($event->isSuccessful()) {
                $payloadData = $event->raw_payload['data'] ?? $event->raw_payload;
                $this->paymentConfirmer->confirm(
                    $event->reference,
                    'webhook',
                    $payloadData
                );
            } else {
                $errorMsg = $event->raw_payload['data']['message'] ?? 'Payment failed';
                $this->paymentService->recordFailedPaymentFromWebhook(
                    $event->reference,
                    $event->gateway_event_id,
                    $event->raw_payload,
                    $errorMsg
                );
            }

            $webhookEvent->markProcessed();
        } catch (\Throwable $e) {
            Log::error('Webhook Processing Error: '.$e->getMessage(), ['exception' => $e]);
            $webhookEvent->markFailed($e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'received' => true,
            'event_id' => $event->gateway_event_id,
            'type' => $event->event_type,
        ]);
    }
}
