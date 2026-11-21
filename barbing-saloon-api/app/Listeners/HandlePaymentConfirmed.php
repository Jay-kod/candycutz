<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Notification\NotificationDispatcher;
use App\Events\PaymentConfirmed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to confirmed payments — notifies the customer
 * and updates appointment payment status.
 */
class HandlePaymentConfirmed implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 10;

    public function handle(PaymentConfirmed $event): void
    {
        $appointment = $event->appointment;

        try {
            app(NotificationDispatcher::class)->paymentVerified($appointment);
        } catch (\Throwable $e) {
            Log::warning("HandlePaymentConfirmed: notification failed for appointment {$appointment->id}: ".$e->getMessage());
        }
    }
}
