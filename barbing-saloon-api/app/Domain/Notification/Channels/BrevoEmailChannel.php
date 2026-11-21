<?php

declare(strict_types=1);

namespace App\Domain\Notification\Channels;

use App\Domain\Notification\Contracts\NotificationChannel;
use App\Jobs\SendBrevoEmail;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Support\Facades\Log;

class BrevoEmailChannel implements NotificationChannel
{
    public function send(User $recipient, Notification $notification, array $context = []): bool
    {
        if (empty($recipient->email)) {
            return false;
        }

        if (! $this->isEnabledFor($recipient, $notification->type)) {
            return false;
        }

        try {
            // Create pending delivery log
            $delivery = NotificationDelivery::create([
                'notification_id' => $notification->id,
                'channel' => 'email',
                'recipient_email' => $recipient->email,
                'status' => 'pending',
                'attempts' => 0,
            ]);

            $html = sprintf(
                '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
                    <h2 style="color: #ff6700;">CandyCutz</h2>
                    <h3>%s</h3>
                    <p style="font-size: 16px; color: #333; line-height: 1.6;">%s</p>
                    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;" />
                    <p style="font-size: 12px; color: #999;">CandyCutz Luxury Grooming · Keffi, Nasarawa State, Nigeria</p>
                </div>',
                htmlspecialchars($notification->title, ENT_QUOTES, 'UTF-8'),
                nl2br(htmlspecialchars($notification->message, ENT_QUOTES, 'UTF-8'))
            );

            SendBrevoEmail::dispatch(
                deliveryId: $delivery->id,
                recipientEmail: $recipient->email,
                subject: $notification->title.' — CandyCutz',
                htmlContent: $html
            );

            return true;
        } catch (\Throwable $e) {
            Log::warning("BrevoEmailChannel failed to queue for user {$recipient->id}: ".$e->getMessage());

            return false;
        }
    }

    public function isEnabledFor(User $recipient, string $type): bool
    {
        // Mandatory transactions always bypass opt-outs
        if (in_array($type, ['payment_failed', 'payment_verified', 'security_alert', 'password_changed', 'account_created'], true)) {
            return true;
        }

        $category = match (true) {
            str_starts_with($type, 'booking_') || str_starts_with($type, 'appointment_') => 'booking',
            str_starts_with($type, 'payment_') => 'payment',
            str_starts_with($type, 'review_') => 'review',
            str_starts_with($type, 'category_') => 'service',
            str_starts_with($type, 'promotion') => 'promotion',
            default => 'announcement',
        };

        // Check granular preferences table first
        $pref = UserNotificationPreference::where('user_id', $recipient->id)
            ->where('category', $category)
            ->where('channel', 'email')
            ->first();

        if ($pref !== null) {
            return (bool) $pref->is_enabled;
        }

        // Fallback to legacy user notification_preferences JSON
        $legacy = $recipient->notification_preferences ?? [];
        if (isset($legacy['email_enabled']) && ! $legacy['email_enabled']) {
            return false;
        }

        return match ($category) {
            'booking' => (bool) ($legacy['notify_bookings'] ?? true),
            'promotion' => (bool) ($legacy['notify_promotions'] ?? true),
            'review' => (bool) ($legacy['notify_reviews'] ?? true),
            default => (bool) ($legacy['notify_general'] ?? true),
        };
    }
}
