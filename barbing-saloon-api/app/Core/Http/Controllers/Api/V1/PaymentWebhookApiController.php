<?php

declare(strict_types=1);

namespace App\Core\Http\Controllers\Api\V1;

use App\Core\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookApiController
{
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $eventType = $payload['type'] ?? 'unknown';

        Log::info("Stripe Webhook Received: {$eventType}", ['payload' => $payload]);

        if ($eventType === 'payment_intent.succeeded') {
            $object = $payload['data']['object'] ?? [];
            $bookingRef = $object['metadata']['booking_reference'] ?? null;

            if ($bookingRef) {
                $appointment = Appointment::where('booking_reference', $bookingRef)->first();
                if ($appointment) {
                    $appointment->update([
                        'status' => AppointmentStatus::confirmed->value,
                        'deposit_paid' => true,
                    ]);
                    Log::info("Appointment {$bookingRef} confirmed via Stripe webhook.");
                }
            }
        }

        return response()->json(['received' => true]);
    }
}
