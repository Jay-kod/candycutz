<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Notification\NotificationDispatcher;
use App\Events\PaymentConfirmed;
use App\Jobs\SendBookingConfirmation;
use App\Models\NotificationDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to confirmed payments — dispatches in-app/push notifications across
 * Customer, Barber, and Admin and sends the luxury booking code & payment receipt email once.
 */
class HandlePaymentConfirmed implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 10;

    public function handle(PaymentConfirmed $event): void
    {
        $appointment = $event->appointment;
        $payment = $event->payment;
        $customerNotificationId = null;

        try {
            $customerNotification = app(NotificationDispatcher::class)->paymentVerified($appointment);
            $customerNotificationId = $customerNotification?->id;
        } catch (\Throwable $e) {
            Log::warning("HandlePaymentConfirmed: notification failed for appointment {$appointment->id}: ".$e->getMessage());
        }

        // Send official Booking Code & Payment Receipt email idempotently
        $cacheKey = "receipt_email_dispatched:{$appointment->id}";
        $cacheClaimed = false;

        try {
            $alreadyTracked = $customerNotificationId !== null && NotificationDelivery::query()
                ->where('notification_id', $customerNotificationId)
                ->where('channel', 'email')
                ->whereIn('status', ['pending', 'processing', 'delivered'])
                ->exists();

            if (! $alreadyTracked && Cache::add($cacheKey, true, now()->addDays(7))) {
                $cacheClaimed = true;
                SendBookingConfirmation::dispatch($appointment, $payment, $customerNotificationId);
            }
        } catch (\Throwable $e) {
            if ($cacheClaimed) {
                Cache::forget($cacheKey);
            }
            Log::warning("HandlePaymentConfirmed: receipt email dispatch failed for appointment {$appointment->id}: ".$e->getMessage());
        }
    }
}
