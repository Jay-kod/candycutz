<?php

declare(strict_types=1);

namespace App\Domain\Notification\Services;

use App\Models\Setting;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;
use RuntimeException;

class BrevoApiClient
{
    /**
     * @param  array<string, mixed>  $to
     */
    public function send(string $recipient, string $subject, string $htmlContent): void
    {
        $settings = Setting::query()
            ->whereIn('key', ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_from', 'mail_from_name'])
            ->pluck('value', 'key');

        $host = (string) ($settings['mail_host'] ?? 'smtp-relay.brevo.com');
        $port = (int) ($settings['mail_port'] ?? 587);
        $username = (string) ($settings['mail_username'] ?? '');
        $password = (string) ($settings['mail_password'] ?? '');
        $senderEmail = (string) ($settings['mail_from'] ?? '');
        $senderName = (string) ($settings['mail_from_name'] ?? 'CandyCutz');

        if ($host === '' || $port < 1 || $username === '' || $password === '' || $senderEmail === '') {
            throw new RuntimeException('Brevo SMTP host, port, login, password, and sender email must be configured.');
        }

        // Brevo port 587 requires STARTTLS before authentication.
        $transport = new EsmtpTransport($host, $port, true);
        $transport->setUsername($username);
        $transport->setPassword($password);

        $email = (new Email())
            ->from(sprintf('%s <%s>', $senderName, $senderEmail))
            ->to($recipient)
            ->subject($subject)
            ->html($htmlContent);

        (new Mailer($transport))->send($email);
    }
}
