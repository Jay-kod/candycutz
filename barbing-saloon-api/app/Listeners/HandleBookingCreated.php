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
 * dispatches in-app + push notifications across Customer, Barber & Admin.
 */
class HandleBookingCreated implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 10;

    public function handle(BookingCreated $event): void
    {
        $appointment = $event->appointment;
        $actor = $event->actor;

        // 1. Confirmation email (if customer has email)
        if ($appointment->customer?->email) {
            try {
                SendBookingConfirmation::dispatch($appointment);
            } catch (\Throwable $e) {
                Log::warning("HandleBookingCreated: confirmation email failed for appointment {$appointment->id}: ".$e->getMessage());
            }
        }

        // 2. In-app + push notifications across Customer, Barber, Admin
        try {
            app(NotificationDispatcher::class)->bookingCreated($appointment, $actor);
        } catch (\Throwable $e) {
            Log::warning("HandleBookingCreated: notification dispatch failed for appointment {$appointment->id}: ".$e->getMessage());
        }
    }
}
