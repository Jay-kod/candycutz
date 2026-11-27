<?php

declare(strict_types=1);

namespace App\Domain\Notification\Channels;

use App\Domain\Notification\Contracts\NotificationChannel;
use App\Jobs\SendExpoPush;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Support\Facades\Log;

class PushNotificationChannel implements NotificationChannel
{
    public function send(User $recipient, Notification $notification, array $context = []): bool
    {
        if (! $this->isEnabledFor($recipient, $notification->type)) {
            return false;
        }

        try {
            $tokens = DeviceToken::where('user_id', $recipient->id)
                ->where('is_revoked', false)
                ->pluck('token')
                ->toArray();

            if (empty($tokens)) {
                return false;
            }

            // Check quiet hours
            if ($this->isQuietHoursActive($recipient, $notification)) {
                NotificationDelivery::create([
                    'notification_id' => $notification->id,
                    'channel' => 'push',
                    'recipient_token' => $tokens[0] ?? null,
                    'status' => 'suppressed',
                    'attempts' => 0,
                    'failure_reason' => 'Suppressed by user quiet hours',
                ]);

                return false;
            }

            $delivery = NotificationDelivery::create([
                'notification_id' => $notification->id,
                'channel' => 'push',
                'recipient_token' => $tokens[0] ?? null,
                'status' => 'pending',
                'attempts' => 0,
            ]);

            SendExpoPush::dispatch(
                tokens: $tokens,
                title: $notification->title,
                body: $notification->message,
                data: [
                    'type' => $notification->type,
                    'notification_id' => $notification->id,
                    'related_entity_id' => $notification->related_entity_id,
                    ...$context,
                ],
                deliveryId: $delivery->id
            );

            return true;
        } catch (\Throwable $e) {
            Log::warning("Failed to queue push notification for user {$recipient->id}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Determine if quiet hours currently apply to suppress non-urgent push notifications.
     */
    protected function isQuietHoursActive(User $recipient, Notification $notification): bool
    {
        // Urgent priority and critical security/payment alerts bypass quiet hours
        if ($notification->priority === 'urgent' || in_array($notification->type, ['security_alert', 'payment_failed', 'password_changed', 'booking_no_show_warning'], true)) {
            return false;
        }

        $category = match (true) {
            str_starts_with($notification->type, 'booking_') || str_starts_with($notification->type, 'appointment_') => 'booking',
            str_starts_with($notification->type, 'payment_') => 'payment',
            str_starts_with($notification->type, 'review_') => 'review',
            str_starts_with($notification->type, 'category_') => 'service',
            str_starts_with($notification->type, 'promotion') => 'promotion',
            default => 'announcement',
        };

        $pref = UserNotificationPreference::where('user_id', $recipient->id)
            ->whereNotNull('quiet_hours_start')
            ->whereNotNull('quiet_hours_end')
            ->first();

        if (! $pref || empty($pref->quiet_hours_start) || empty($pref->quiet_hours_end)) {
            return false;
        }

        $currentTime = now()->format('H:i');
        $start = $pref->quiet_hours_start;
        $end = $pref->quiet_hours_end;

        if ($start <= $end) {
            return $currentTime >= $start && $currentTime <= $end;
        }

        // Overnight quiet hours (e.g. 22:00 to 07:00)
        return $currentTime >= $start || $currentTime <= $end;
    }

    public function isEnabledFor(User $recipient, string $type): bool
    {
        // Critical transaction/security types always bypass opt-outs
        if (in_array($type, [
            'payment_failed',
            'payment_verified',
            'security_alert',
            'password_changed',
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
            ->where('channel', 'push')
            ->first();

        if ($pref !== null) {
            return (bool) $pref->is_enabled;
        }

        $prefs = $recipient->notification_preferences ?? [];

        // Check if push is globally disabled in legacy settings
        if (isset($prefs['push_enabled']) && ! $prefs['push_enabled']) {
            return false;
        }

        if (str_starts_with($type, 'booking_') || str_starts_with($type, 'appointment_')) {
            return (bool) ($prefs['notify_bookings'] ?? $prefs['notify_appointments'] ?? true);
        }

        if (str_starts_with($type, 'blog_')) {
            return (bool) ($prefs['notify_blog'] ?? true);
        }

        if (str_starts_with($type, 'wishlist_')) {
            return (bool) ($prefs['notify_wishlist'] ?? true);
        }

        if (in_array($type, ['promotion', 'promotional'], true)) {
            return (bool) ($prefs['notify_promotions'] ?? true);
        }

        if (str_starts_with($type, 'system_')) {
            return (bool) ($prefs['notify_system'] ?? true);
        }

        return (bool) ($prefs['notify_general'] ?? true);
    }
}
