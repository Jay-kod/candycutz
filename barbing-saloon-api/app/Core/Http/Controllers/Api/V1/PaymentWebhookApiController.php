<?php

declare(strict_types=1);

namespace App\Core\Http\Controllers\Api\V1;

use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class PaymentWebhookApiController
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $rawPayload = (string) $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret') ?: env('STRIPE_WEBHOOK_SECRET');

        $event = null;

        // If webhook secret is configured and signature header exists, verify cryptographic signature
        if ($webhookSecret && !str_starts_with($webhookSecret, 'whsec_placeholder') && $sigHeader) {
            try {
                $event = Webhook::constructEvent($rawPayload, $sigHeader, $webhookSecret);
            } catch (UnexpectedValueException $e) {
                Log::warning('Stripe Webhook Invalid Payload: ' . $e->getMessage());
                return response()->json(['error' => 'Invalid payload'], 400);
            } catch (SignatureVerificationException $e) {
                Log::warning('Stripe Webhook Signature Verification Failed: ' . $e->getMessage());
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        // Parse payload as array for data extraction
        $parsed = json_decode($rawPayload, true) ?: $request->all();
        $eventType = $event ? $event->type : ($parsed['type'] ?? 'unknown');
        $eventId = $event ? $event->id : ($parsed['id'] ?? ('evt_' . bin2hex(random_bytes(8))));
        $dataObject = $event ? (array) $event->data->object : ($parsed['data']['object'] ?? []);

        Log::info("Stripe Webhook Received: {$eventType} [Event: {$eventId}]");

        try {
            switch ($eventType) {
                case 'payment_intent.succeeded':
                    $paymentIntentId = $dataObject['id'] ?? ($dataObject['payment_intent'] ?? null);
                    if ($paymentIntentId) {
                        $this->paymentService->confirmPaymentFromWebhook((string) $paymentIntentId, (string) $eventId, $parsed);
                    }
                    break;

                case 'checkout.session.completed':
                    $paymentIntentId = $dataObject['payment_intent'] ?? ($dataObject['id'] ?? null);
                    if ($paymentIntentId) {
                        $this->paymentService->confirmPaymentFromWebhook((string) $paymentIntentId, (string) $eventId, $parsed);
                    }
                    break;

                case 'payment_intent.payment_failed':
                    $paymentIntentId = $dataObject['id'] ?? null;
                    $errorMsg = $dataObject['last_payment_error']['message'] ?? 'Payment failed';
                    if ($paymentIntentId) {
                        $this->paymentService->recordFailedPaymentFromWebhook((string) $paymentIntentId, (string) $eventId, $parsed, $errorMsg);
                    }
                    break;

                default:
                    Log::info("Unhandled Stripe webhook event type: {$eventType}");
                    break;
            }
        } catch (\Throwable $e) {
            Log::error("Stripe Webhook Processing Error: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'received' => true,
            'event_id' => $eventId,
            'type' => $eventType,
        ]);
    }
}
