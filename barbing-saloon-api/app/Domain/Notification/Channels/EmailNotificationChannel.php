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

            $html = view('mail.general-notification', [
                'recipient' => $recipient,
                'notification' => $notification,
                'title' => $notification->title,
                'message' => $notification->message,
            ])->render();

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
