<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Notification\NotificationDispatcher;
use App\Events\BookingCreated;
use App\Jobs\SendBookingConfirmation;
use App\Models\NotificationDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to new bookings — dispatches in-app + push notifications across
 * Customer, Barber & Admin, and sends confirmation email immediately only when pre-paid.
 */
class HandleBookingCreated implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 10;

    public function handle(BookingCreated $event): void
    {
        $appointment = $event->appointment;
        $actor = $event->actor;

        // 1. In-app + push notifications across Customer, Barber, Admin
        try {
            app(NotificationDispatcher::class)->bookingCreated($appointment, $actor);
        } catch (\Throwable $e) {
            Log::warning("HandleBookingCreated: notification dispatch failed for appointment {$appointment->id}: ".$e->getMessage());
        }

        // 2. If already paid upon creation, send the official booking code & receipt email idempotently
        if ($appointment->deposit_paid) {
            $cacheKey = "receipt_email_dispatched:{$appointment->id}";
            $alreadyTracked = NotificationDelivery::query()
                ->where('channel', 'email')
                ->whereIn('status', ['pending', 'processing', 'delivered'])
                ->whereHas('notification', fn ($q) => $q->where('related_entity_id', $appointment->id)->where('type', 'payment_verified'))
                ->exists();

            if (! $alreadyTracked && Cache::add($cacheKey, true, now()->addDays(7))) {
                try {
                    SendBookingConfirmation::dispatch($appointment, $appointment->payment);
                } catch (\Throwable $e) {
                    Cache::forget($cacheKey);
                    Log::warning("HandleBookingCreated: confirmation email failed for appointment {$appointment->id}: ".$e->getMessage());
                }
            }
        }
    }
}
