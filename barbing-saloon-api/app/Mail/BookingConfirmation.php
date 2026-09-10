<?php

namespace App\Mail;

use App\Models\Appointment;
use Carbon\Carbon;
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
        $this->appointment = $appointment->load(['customer', 'barber.user', 'service']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your CandyCutz Booking is Confirmed',
        );
    }

    public function content(): Content
    {
        $dateStr = $this->appointment->appointment_date
            ? Carbon::parse($this->appointment->appointment_date)->format('M d, Y')
            : now()->format('M d, Y');

        $timeStr = $this->appointment->appointment_time;
        $formattedTime = $timeStr ? Carbon::parse($timeStr)->format('g:i A') : 'Scheduled Time';

        return new Content(
            view: 'mail.booking-confirmation',
            with: [
                'appointment' => $this->appointment,
                'customerName' => $this->appointment->customer?->name ?? ($this->appointment->client_name ?? 'Valued Customer'),
                'barberName' => $this->appointment->barber?->user?->name ?? 'Our Team',
                'serviceName' => $this->appointment->service?->name ?? 'Grooming Service',
                'appointmentDate' => $dateStr,
                'appointmentTime' => $formattedTime,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
