<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Notification\NotificationDispatcher;
use App\Events\AppointmentStatusChanged;
use App\Jobs\SendBookingCancellation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to appointment status transitions by dispatching
 * in-app notifications, push notifications, and emails.
 *
 * This listener decouples BookingService from notification concerns.
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

        // 1. Status-specific in-app + push notifications
        try {
            $dispatcher = app(NotificationDispatcher::class);

            match ($newStatus) {
                'confirmed' => $dispatcher->bookingApproved($appointment),
                'cancelled' => $dispatcher->bookingCancelled($appointment, $actor),
                'completed' => $dispatcher->bookingCompleted($appointment),
                'no_show' => $dispatcher->bookingNoShow($appointment),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::warning("HandleAppointmentStatusChanged: notification dispatch failed for appointment {$appointment->id}: ".$e->getMessage());
        }

        // 2. Cancellation email
        if ($newStatus === 'cancelled' && $appointment->customer) {
            try {
                SendBookingCancellation::dispatch($appointment);
            } catch (\Throwable $e) {
                Log::warning("HandleAppointmentStatusChanged: cancellation email failed for appointment {$appointment->id}: ".$e->getMessage());
            }
        }
    }
}
