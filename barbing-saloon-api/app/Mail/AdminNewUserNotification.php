<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewUserNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $provider = 'email'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "CandyCutz Admin Alert: New User Registration ({$this->user->name})",
        );
    }

    public function content(): Content
    {
        $frontendUrl = (string) config('app.frontend_url', config('app.url', 'http://localhost:5173'));

        $providerLabel = match (strtolower($this->provider)) {
            'google' => 'Google OAuth',
            'admin' => 'Admin Direct Registration',
            default => 'Email & Password',
        };

        return new Content(
            view: 'mail.admin-new-user',
            with: [
                'user' => $this->user,
                'provider' => $this->provider,
                'providerLabel' => $providerLabel,
                'registeredAt' => now()->format('M d, Y • g:i A T'),
                'adminUrl' => rtrim($frontendUrl, '/').'/admin/customers',
            ],
        );
    }

    /** @return array<int, \Illuminate\Mail\Mailables\Attachment> */
    public function attachments(): array
    {
        return [];
    }
}
