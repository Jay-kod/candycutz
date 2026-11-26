<?php

declare(strict_types=1);

namespace App\Domain\Notification\Services;

use App\Domain\Notification\Channels\EmailNotificationChannel;
use App\Models\Appointment;
use App\Models\NotificationDelivery;
use App\Support\Errors\ErrorReporter;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Testing\Fakes\MailFake;

/**
 * Unified runner for queued appointment Mailables with Resend config hydration,
 * client-email fallback, optional delivery telemetry, and structured error reporting.
 */
class TransactionalMailRunner
{
    public const WALKIN_PLACEHOLDER_EMAIL = 'walkin@candycutz.com';

    /**
     * Resolve a valid recipient email for an appointment.
     */
    public static function resolveRecipientEmail(Appointment $appointment): ?string
    {
        $appointment->loadMissing(['customer']);
        $email = trim((string) ($appointment->customer?->email ?: $appointment->client_email ?: ''));

        if ($email === '' || strcasecmp($email, self::WALKIN_PLACEHOLDER_EMAIL) === 0) {
            return null;
        }

        return $email;
    }

    /**
     * Resolve customer display name for email greetings.
     */
    public static function resolveRecipientName(Appointment $appointment): string
    {
        $appointment->loadMissing(['customer']);

        return (string) ($appointment->customer?->real_name
            ?: $appointment->customer?->name
            ?: $appointment->client_name
            ?: 'Valued Customer');
    }

    /**
     * Send an appointment Mailable and update delivery telemetry when linked to a Notification.
     */
    public function send(
        Appointment $appointment,
        Mailable $mailable,
        string $failureLabel,
        ?int $notificationId = null,
        bool $respectPreferences = false,
        string $preferenceType = 'booking_created'
    ): void {
        $email = self::resolveRecipientEmail($appointment);
        if (! $email) {
            return;
        }

        if ($respectPreferences && $appointment->customer) {
            $channel = app(EmailNotificationChannel::class);
            if (! $channel->isEnabledFor($appointment->customer, $preferenceType)) {
                return;
            }
        }

        ResendMailConfigurator::hydrate(true);

        $delivery = null;
        if ($notificationId !== null) {
            try {
                $delivery = NotificationDelivery::query()
                    ->where('notification_id', $notificationId)
                    ->where('channel', 'email')
                    ->first();

                if ($delivery && $delivery->status === 'delivered') {
                    return;
                }

                if ($delivery) {
                    $delivery->update([
                        'recipient_email' => $email,
                        'status' => 'processing',
                        'attempts' => ((int) $delivery->attempts) + 1,
                        'last_attempt_at' => now(),
                    ]);
                } else {
                    $delivery = NotificationDelivery::create([
                        'notification_id' => $notificationId,
                        'channel' => 'email',
                        'recipient_email' => $email,
                        'status' => 'processing',
                        'attempts' => 1,
                        'last_attempt_at' => now(),
                    ]);
                }
            } catch (\Throwable) {
                $delivery = null;
            }
        }

        try {
            $pendingMail = Mail::to($email);
            if (Mail::getFacadeRoot() instanceof MailFake) {
                $pendingMail->send($mailable);
            } else {
                // Execute inside the already-queued job so delivery telemetry and retries track actual transport
                $pendingMail->sendNow($mailable);
            }

            if ($delivery) {
                $delivery->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                    'failure_code' => null,
                    'failure_reason' => null,
                    'provider_reference' => 'resend-'.uniqid(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("Failed sending {$failureLabel}", [
                'recipient' => $email,
                'mailable' => $mailable::class,
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            if ($delivery) {
                try {
                    $delivery->update([
                        'status' => 'failed',
                        'failure_code' => 'EMAIL_SEND_FAILED',
                        'failure_reason' => $e->getMessage(),
                    ]);
                } catch (\Throwable) {
                    // Preserve original mail failure
                }
            }

            try {
                app(ErrorReporter::class)->report($e);
            } catch (\Throwable) {
                // Safeguard against reporter failure
            }

            throw $e;
        }
    }
}
