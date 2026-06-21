<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingThankYou extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Appointment $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment->load(['customer', 'barber', 'service']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thank You for Visiting CandyCutz',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.booking-thankyou',
            with: [
                'appointment' => $this->appointment,
                'customerName' => $this->appointment->customer->name,
                'barberName' => $this->appointment->barber?->user->name ?? 'Our Team',
                'serviceName' => $this->appointment->service->name,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
