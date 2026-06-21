<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Appointment $appointment;
    public string $action;

    public function __construct(Appointment $appointment, string $action = 'new_booking')
    {
        $this->appointment = $appointment->load(['customer', 'barber', 'service']);
        $this->action = $action;
    }

    public function envelope(): Envelope
    {
        $actionText = match($this->action) {
            'new_booking' => 'New Booking',
            'cancellation' => 'Booking Cancellation',
            'completed' => 'Booking Completed',
            'no_show' => 'No Show',
            default => 'Booking Update',
        };

        return new Envelope(
            subject: "CandyCutz Admin Alert: {$actionText}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.admin-notification',
            with: [
                'appointment' => $this->appointment,
                'action' => $this->action,
                'customerName' => $this->appointment->customer->name,
                'customerEmail' => $this->appointment->customer->email,
                'barberName' => $this->appointment->barber?->user->name ?? 'Unassigned',
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
