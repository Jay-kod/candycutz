<?php

declare(strict_types=1);

namespace App\Domain\Notification\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BrevoApiClient
{
    /**
     * @param  array<string, mixed>  $to
     */
    public function send(string $recipient, string $subject, string $htmlContent): void
    {
        $apiKey = (string) Setting::query()->where('key', 'brevo_api_key')->value('value');
        $senderEmail = (string) Setting::query()->where('key', 'mail_from')->value('value');
        $senderName = (string) (Setting::query()->where('key', 'mail_from_name')->value('value') ?: 'CandyCutz');

        if ($apiKey === '' || $senderEmail === '') {
            throw new RuntimeException('Brevo API key and sender email must be configured.');
        }

        $response = Http::timeout(15)
            ->withHeaders([
                'accept' => 'application/json',
                'api-key' => $apiKey,
                'content-type' => 'application/json',
            ])
            ->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => ['email' => $senderEmail, 'name' => $senderName],
                'to' => [['email' => $recipient]],
                'subject' => $subject,
                'htmlContent' => $htmlContent,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Brevo rejected the email request.');
        }
    }
}
