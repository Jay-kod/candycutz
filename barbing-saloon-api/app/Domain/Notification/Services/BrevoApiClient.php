<?php

declare(strict_types=1);

namespace App\Domain\Notification\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;

class BrevoApiClient
{
    public function send(string $recipient, string $subject, string $htmlContent): void
    {
        $settings = Setting::query()
            ->whereIn('key', ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_from', 'mail_from_name', 'brevo_api_key'])
            ->pluck('value', 'key');

        $host = (string) ($settings['mail_host'] ?? config('mail.mailers.smtp.host') ?? 'smtp-relay.brevo.com');
        $port = (int) ($settings['mail_port'] ?? config('mail.mailers.smtp.port') ?? 587);
        $username = (string) ($settings['mail_username'] ?? config('mail.mailers.smtp.username') ?? '');
        $password = (string) ($settings['mail_password'] ?? config('mail.mailers.smtp.password') ?? '');
        $senderEmail = (string) ($settings['mail_from'] ?? config('mail.from.address') ?? '');
        $senderName = (string) ($settings['mail_from_name'] ?? config('mail.from.name') ?? 'CandyCutz');
        $apiKey = (string) ($settings['brevo_api_key'] ?? config('services.brevo.api_key') ?? '');

        // If direct Brevo REST API key is provided and SMTP credentials are missing, send via REST API
        if ($apiKey !== '' && ($username === '' || $password === '')) {
            $this->sendViaRestApi($apiKey, $senderName, $senderEmail, $recipient, $subject, $htmlContent);
            return;
        }

        if ($host === '' || $port < 1 || $username === '' || $password === '' || $senderEmail === '') {
            throw new RuntimeException('Brevo SMTP host, port, login, password, and sender email must be configured in settings or mail config.');
        }

        try {
            // Port 465 uses direct SSL; Port 587 uses STARTTLS (plain TCP initially upgraded via STARTTLS)
            $isDirectSsl = ($port === 465);
            $transport = new EsmtpTransport($host, $port, $isDirectSsl);
            $transport->setUsername($username);
            $transport->setPassword($password);

            if ($port === 587 || $isDirectSsl) {
                $transport->setRequireTls(true);
            }

            $email = (new Email)
                ->from(sprintf('%s <%s>', $senderName, $senderEmail))
                ->to($recipient)
                ->subject($subject)
                ->html($htmlContent);

            (new Mailer($transport))->send($email);
        } catch (\Throwable $smtpException) {
            // If SMTP fails (e.g. host firewalled on ports 25/587) and REST API key is configured, fallback to REST API
            if ($apiKey !== '') {
                Log::warning("Brevo SMTP failed ({$smtpException->getMessage()}), falling back to Brevo REST API.");
                $this->sendViaRestApi($apiKey, $senderName, $senderEmail, $recipient, $subject, $htmlContent);
                return;
            }

            throw $smtpException;
        }
    }

    private function sendViaRestApi(string $apiKey, string $senderName, string $senderEmail, string $recipient, string $subject, string $htmlContent): void
    {
        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->timeout(15)
        ->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => $senderName,
                'email' => $senderEmail,
            ],
            'to' => [
                ['email' => $recipient],
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ]);

        if (! $response->successful()) {
            $errorMsg = $response->json('message') ?? $response->body() ?? 'Brevo REST API request failed';
            throw new RuntimeException("Brevo API error: {$errorMsg}");
        }
    }
}
