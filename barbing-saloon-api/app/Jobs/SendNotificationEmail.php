<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\NotificationDelivery;
use App\Models\Setting;
use App\Support\Errors\ErrorReporter;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 15;

    public function __construct(
        public int $deliveryId,
        public string $recipientEmail,
        public string $subject,
        public string $htmlContent,
    ) {}

    public function handle(): void
    {
        $resendKey = Setting::where('key', 'resend_api_key')->value('value');
        if (! empty($resendKey)) {
            $apiKey = (string) $resendKey;
            config([
                'services.resend.key' => $apiKey,
                'mail.mailers.resend.key' => $apiKey,
            ]);
            if (! app()->runningUnitTests()) {
                config(['mail.default' => 'resend']);
                if (app()->bound('mail.manager')) {
                    Mail::purge('resend');
                }
            }
        }

        $delivery = NotificationDelivery::find($this->deliveryId);

        if ($delivery) {
            $delivery->update([
                'status' => 'processing',
                'attempts' => $delivery->attempts + 1,
                'last_attempt_at' => now(),
            ]);
        }

        try {
            Mail::html($this->htmlContent, function ($message) {
                $message->to($this->recipientEmail)
                    ->subject($this->subject);
            });

            if ($delivery) {
                $delivery->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                    'provider_reference' => 'resend-'.uniqid(),
                ]);
            }
        } catch (Exception $e) {
            Log::error('Notification email delivery failed', [
                'recipient' => $this->recipientEmail,
                'subject' => $this->subject,
                'delivery_id' => $this->deliveryId,
                'error' => $e->getMessage(),
            ]);

            try {
                app(ErrorReporter::class)->report($e);
            } catch (\Throwable) {
                // Keep resilient if ErrorReporter itself encounters an issue
            }

            if ($delivery) {
                $delivery->update([
                    'status' => 'failed',
                    'failure_code' => 'EMAIL_SEND_FAILED',
                    'failure_reason' => $e->getMessage(),
                ]);
            }

            throw $e; // Allow queue retry
        }
    }
}
