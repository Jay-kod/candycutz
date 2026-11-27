<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Notification\NotificationDispatcher;
use App\Events\AppointmentStatusChanged;
use App\Jobs\SendBookingCancellation;
use App\Jobs\SendBookingThankYou;
use App\Models\NotificationDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to appointment status transitions by dispatching
 * in-app notifications, push notifications, and luxury emails across
 * the core triad: Barber, Customer, and Admin.
 */
class HandleAppointmentStatusChanged implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 10;

    public function handle(AppointmentStatusChanged $event): void
    {
        $appointment = $event->appointment;
        $newStatus = $event->newStatus;
        $actor = $event->actor;
        $customerNotification = null;

        // 1. Status-specific in-app + push notifications across Customer, Barber & Admin
        try {
            $dispatcher = app(NotificationDispatcher::class);

            match ($newStatus) {
                'confirmed' => $dispatcher->bookingApproved($appointment, $actor),
                'cancelled' => $dispatcher->bookingCancelled($appointment, $actor),
                'completed' => $customerNotification = $dispatcher->bookingCompleted($appointment, $actor),
                'no_show' => $dispatcher->bookingNoShow($appointment, $actor),
                'checked_in' => $dispatcher->bookingCheckedIn($appointment, $actor),
                'in_progress' => $dispatcher->bookingInProgress($appointment, $actor),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::warning("HandleAppointmentStatusChanged: notification dispatch failed for appointment {$appointment->id}: ".$e->getMessage());
        }

        // 2. Session completion summary & thank-you email
        if ($newStatus === 'completed') {
            $cacheKey = "completion_email_dispatched:{$appointment->id}";
            $cacheClaimed = false;

            try {
                $customerNotificationId = $customerNotification?->id;
                $alreadyTracked = $customerNotificationId !== null && NotificationDelivery::query()
                    ->where('notification_id', $customerNotificationId)
                    ->where('channel', 'email')
                    ->whereIn('status', ['pending', 'processing', 'delivered'])
                    ->exists();

                if (! $alreadyTracked && Cache::add($cacheKey, true, now()->addDays(7))) {
                    $cacheClaimed = true;
                    SendBookingThankYou::dispatch($appointment, $customerNotificationId);
                }
            } catch (\Throwable $e) {
                if ($cacheClaimed) {
                    Cache::forget($cacheKey);
                }
                Log::warning("HandleAppointmentStatusChanged: completion email failed for appointment {$appointment->id}: ".$e->getMessage());
            }
        }

        // 3. Cancellation email
        if ($newStatus === 'cancelled') {
            try {
                SendBookingCancellation::dispatch($appointment);
            } catch (\Throwable $e) {
                Log::warning("HandleAppointmentStatusChanged: cancellation email failed for appointment {$appointment->id}: ".$e->getMessage());
            }
        }
    }
}
