<?php

declare(strict_types=1);

namespace App\Domain\Notification\Channels;

use App\Domain\Notification\Contracts\NotificationChannel;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Support\Facades\Log;

class InAppNotificationChannel implements NotificationChannel
{
    /**
     * In-app notifications are stored in the database.
     * Record an in_app delivery record to track authoritative receipt.
     */
    public function send(User $recipient, Notification $notification, array $context = []): bool
    {
        if (! $this->isEnabledFor($recipient, $notification->type)) {
            return false;
        }

        try {
            NotificationDelivery::firstOrCreate(
                [
                    'notification_id' => $notification->id,
                    'channel' => 'in_app',
                ],
                [
                    'status' => 'delivered',
                    'attempts' => 1,
                    'delivered_at' => now(),
                ]
            );

            return true;
        } catch (\Throwable $e) {
            Log::warning("InAppNotificationChannel delivery log failed for user {$recipient->id}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Check if user preferences allow notifications of this type.
     */
    public function isEnabledFor(User $recipient, string $type): bool
    {
        // Critical transaction/security types always bypass opt-outs
        if (in_array($type, [
            'payment_failed',
            'payment_verified',
            'security_alert',
            'password_changed',
            'account_created',
            'booking_no_show_warning',
            'booking_no_show',
            'refund_initiated',
            'refund_completed',
            'refund_requested',
            'refund_approved',
            'refund_rejected',
        ], true)) {
            return true;
        }

        $category = match (true) {
            str_starts_with($type, 'booking_') || str_starts_with($type, 'appointment_') => 'booking',
            str_starts_with($type, 'payment_') || str_starts_with($type, 'refund_') => 'payment',
            str_starts_with($type, 'review_') => 'review',
            str_starts_with($type, 'category_') => 'service',
            str_starts_with($type, 'promotion') => 'promotion',
            default => 'announcement',
        };

        // Check granular preferences table first
        $pref = UserNotificationPreference::where('user_id', $recipient->id)
            ->where('category', $category)
            ->where('channel', 'in_app')
            ->first();

        if ($pref !== null) {
            return (bool) $pref->is_enabled;
        }

        // Fallback to legacy user notification_preferences JSON
        $prefs = $recipient->notification_preferences ?? [];

        if (str_starts_with($type, 'booking_') || str_starts_with($type, 'appointment_')) {
            return (bool) ($prefs['notify_bookings'] ?? $prefs['notify_appointments'] ?? true);
        }

        if (str_starts_with($type, 'blog_')) {
            return (bool) ($prefs['notify_blog'] ?? true);
        }

        if (str_starts_with($type, 'wishlist_')) {
            return (bool) ($prefs['notify_wishlist'] ?? true);
        }

        if (in_array($type, ['promotion', 'promotional', 'discount'], true)) {
            return (bool) ($prefs['notify_promotions'] ?? true);
        }

        if (str_starts_with($type, 'system_')) {
            return (bool) ($prefs['notify_system'] ?? true);
        }

        return (bool) ($prefs['notify_general'] ?? true);
    }
}
