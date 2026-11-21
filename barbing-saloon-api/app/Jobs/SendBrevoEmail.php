<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Notification\Services\BrevoApiClient;
use App\Models\NotificationDelivery;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBrevoEmail implements ShouldQueue
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

    public function handle(BrevoApiClient $brevo): void
    {
        $delivery = NotificationDelivery::find($this->deliveryId);

        if ($delivery) {
            $delivery->update([
                'status' => 'processing',
                'attempts' => $delivery->attempts + 1,
                'last_attempt_at' => now(),
            ]);
        }

        try {
            // Attempt dispatch via BrevoApiClient SMTP/STARTTLS
            $brevo->send($this->recipientEmail, $this->subject, $this->htmlContent);

            if ($delivery) {
                $delivery->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                    'provider_reference' => 'brevo-smtp-'.uniqid(),
                ]);
            }
        } catch (Exception $e) {
            Log::warning("SendBrevoEmail delivery #{$this->deliveryId} failed: ".$e->getMessage());

            if ($delivery) {
                $delivery->update([
                    'status' => 'failed',
                    'failure_code' => 'BREVO_SEND_ERROR',
                    'failure_reason' => $e->getMessage(),
                ]);
            }

            throw $e; // Allow queue retry
        }
    }
}
