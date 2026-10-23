<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PaymentFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to failed payments — logs the failure and notifies
 * the customer with a retry prompt.
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

        // Future: Notify customer via NotificationDispatcher once paymentFailed() method is added
    }
}
