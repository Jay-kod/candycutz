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

class WelcomeCustomer extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to CandyCutz Luxury Grooming 💈',
        );
    }

    public function content(): Content
    {
        $frontendUrl = (string) config('app.frontend_url', config('app.url', 'http://localhost:5173'));

        return new Content(
            view: 'mail.welcome-customer',
            with: [
                'user' => $this->user,
                'userName' => $this->user->name ?: 'Valued Client',
                'dashboardUrl' => rtrim($frontendUrl, '/').'/customer/dashboard',
                'servicesUrl' => rtrim($frontendUrl, '/').'/services',
            ],
        );
    }

    /** @return array<int, \Illuminate\Mail\Mailables\Attachment> */
    public function attachments(): array
    {
        return [];
    }
}
