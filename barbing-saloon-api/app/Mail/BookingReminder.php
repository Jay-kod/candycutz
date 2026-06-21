<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingReminder extends Mailable implements ShouldQueue
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
            subject: 'Reminder: Your CandyCutz Appointment is Tomorrow',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.booking-reminder',
            with: [
                'appointment' => $this->appointment,
                'customerName' => $this->appointment->customer->name,
                'barberName' => $this->appointment->barber?->user->name ?? 'Our Team',
                'serviceName' => $this->appointment->service->name,
                'appointmentTime' => $this->appointment->appointment_date->format('g:i A'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
