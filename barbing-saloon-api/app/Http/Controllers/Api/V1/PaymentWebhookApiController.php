<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookApiController
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PaymentGateway $gateway
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $rawPayload = (string) $request->getContent();
        $headers = $request->headers->all();

        try {
            $event = $this->gateway->handleWebhook($rawPayload, $headers);
        } catch (\Throwable $e) {
            Log::warning('Webhook Verification Failed: '.$e->getMessage());

            return response()->json(['error' => 'Invalid signature or payload: '.$e->getMessage()], 400);
        }

        Log::info("Webhook Received: {$event->event_type} [Event: {$event->gateway_event_id}]");

        try {
            if ($event->isSuccessful()) {
                $this->paymentService->confirmPaymentFromWebhook(
                    $event->reference,
                    $event->gateway_event_id,
                    $event->raw_payload
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
        } catch (\Throwable $e) {
            Log::error('Webhook Processing Error: '.$e->getMessage(), ['exception' => $e]);

            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'received' => true,
            'event_id' => $event->gateway_event_id,
            'type' => $event->event_type,
        ]);
    }
}
