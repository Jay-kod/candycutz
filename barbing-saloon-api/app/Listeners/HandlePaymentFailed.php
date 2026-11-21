<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Notification\NotificationDispatcher;
use App\Events\PaymentFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to failed payments — logs the failure and notifies
 * the customer, barber, and admins.
 */
class HandlePaymentFailed implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 10;

    public function handle(PaymentFailed $event): void
    {
        $payment = $event->payment;
        $reason = $event->reason;

        Log::error("Payment failed for payment #{$payment->id}: {$reason}", [
            'payment_id' => $payment->id,
            'reason' => $reason,
        ]);

        if ($payment->appointment) {
            try {
                app(NotificationDispatcher::class)->paymentFailed($payment->appointment, $reason);
            } catch (\Throwable $e) {
                Log::warning("HandlePaymentFailed: notification failed for appointment {$payment->appointment->id}: ".$e->getMessage());
            }
        }
    }
}
