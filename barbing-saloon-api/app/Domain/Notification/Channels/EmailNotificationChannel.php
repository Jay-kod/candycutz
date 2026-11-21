<?php

declare(strict_types=1);

namespace App\Domain\Notification\Channels;

use App\Domain\Notification\Contracts\NotificationChannel;
use App\Jobs\SendNotificationEmail;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\User;
use App\Models\UserNotificationPreference;
use App\Support\Errors\ErrorReporter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class EmailNotificationChannel implements NotificationChannel
{
    public function send(User $recipient, Notification $notification, array $context = []): bool
    {
        if (empty($recipient->email)) {
            return false;
        }

        if (! $this->isEnabledFor($recipient, $notification->type)) {
            return false;
        }

        // Rate-limit guard against duplicate dispatches to the same recipient in a short window
        $throttleKey = "email-dispatch:{$recipient->id}:{$notification->type}:{$notification->id}";
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            Log::info("Email notification suppressed as duplicate for user {$recipient->id}", [
                'type' => $notification->type,
                'email' => $recipient->email,
            ]);

            return false;
        }
        RateLimiter::hit($throttleKey, 5);

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
                '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #222; border-radius: 12px; background: #0c0e12; color: #f1f5f9;">
                    <div style="margin-bottom: 20px;">
                        <span style="font-weight: 800; font-size: 20px; color: #ff6700; letter-spacing: -0.5px;">CandyCutz</span>
                        <span style="font-size: 11px; text-transform: uppercase; color: #94a3b8; margin-left: 8px; letter-spacing: 1px;">Luxury Grooming</span>
                    </div>
                    <h2 style="color: #ffffff; font-size: 18px; margin-top: 0;">%s</h2>
                    <p style="font-size: 15px; color: #cbd5e1; line-height: 1.6;">%s</p>
                    <hr style="border: none; border-top: 1px solid #1e293b; margin: 24px 0;" />
                    <p style="font-size: 11px; color: #64748b; line-height: 1.5; margin: 0;">
                        CandyCutz Luxury Grooming · Keffi, Nasarawa State, Nigeria<br />
                        This email was sent to %s.
                    </p>
                </div>',
                htmlspecialchars($notification->title, ENT_QUOTES, 'UTF-8'),
                nl2br(htmlspecialchars($notification->message, ENT_QUOTES, 'UTF-8')),
                htmlspecialchars($recipient->email, ENT_QUOTES, 'UTF-8')
            );

            SendNotificationEmail::dispatch(
                deliveryId: $delivery->id,
                recipientEmail: $recipient->email,
                subject: $notification->title.' — CandyCutz',
                htmlContent: $html
            );

            return true;
        } catch (\Throwable $e) {
            Log::error("EmailNotificationChannel failed to queue for user {$recipient->id}", [
                'recipient' => $recipient->email,
                'error' => $e->getMessage(),
            ]);

            try {
                app(ErrorReporter::class)->report($e);
            } catch (\Throwable) {
                // Safeguard against error reporter failures
            }

            return false;
        }
    }

    public function isEnabledFor(User $recipient, string $type): bool
    {
        // Mandatory security and transactional messages always bypass opt-outs
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
