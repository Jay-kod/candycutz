<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Notification\NotificationDispatcher;
use App\Events\BookingCreated;
use App\Jobs\SendBookingConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to new bookings — sends confirmation email and
 * dispatches in-app + push notifications to customer and barber.
 */
class HandleBookingCreated implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 10;

    public function handle(BookingCreated $event): void
    {
        $appointment = $event->appointment;

        // 1. Confirmation email
        try {
            SendBookingConfirmation::dispatch($appointment);
        } catch (\Throwable $e) {
            Log::warning("HandleBookingCreated: confirmation email failed for appointment {$appointment->id}: ".$e->getMessage());
        }

        // 2. In-app + push notifications
        try {
            app(NotificationDispatcher::class)->bookingCreated($appointment);
        } catch (\Throwable $e) {
            Log::warning("HandleBookingCreated: notification dispatch failed for appointment {$appointment->id}: ".$e->getMessage());
        }
    }
}
