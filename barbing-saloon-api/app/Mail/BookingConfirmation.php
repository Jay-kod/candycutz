<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable implements ShouldQueue
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
            subject: 'Your CandyCutz Booking is Confirmed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.booking-confirmation',
            with: [
                'appointment' => $this->appointment,
                'customerName' => $this->appointment->customer->name,
                'barberName' => $this->appointment->barber?->user->name ?? 'Our Team',
                'serviceName' => $this->appointment->service->name,
                'appointmentDate' => $this->appointment->appointment_date->format('M d, Y'),
                'appointmentTime' => $this->appointment->appointment_date->format('g:i A'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
