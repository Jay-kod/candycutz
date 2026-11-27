<?php

declare(strict_types=1);

namespace App\Mail;

use App\Domain\Notification\Services\TransactionalMailRunner;
use App\Domain\Shared\Services\SalonClock;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NoShowWarning extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Appointment $appointment;

    public int $minutesRemaining;

    public bool $canExtend;

    public function __construct(Appointment $appointment, int $minutesRemaining = 5, bool $canExtend = true)
    {
        $this->appointment = $appointment->load(['customer', 'barber.user', 'service', 'serviceZone']);
        $this->minutesRemaining = max(1, $minutesRemaining);
        $this->canExtend = $canExtend;
    }

    public function envelope(): Envelope
    {
        $bookingCode = $this->appointment->verification_code;

        return new Envelope(
            subject: "Urgent: Check In Within {$this->minutesRemaining} Minutes or Risk No-Show ({$bookingCode})",
        );
    }

    public function content(): Content
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url', 'http://localhost:5173')), '/');

        return new Content(
            view: 'mail.no-show-warning',
            with: [
                'appointment' => $this->appointment,
                'minutesRemaining' => $this->minutesRemaining,
                'canExtend' => $this->canExtend,
                'bookingCode' => $this->appointment->verification_code,
                'customerName' => TransactionalMailRunner::resolveRecipientName($this->appointment),
                'barberName' => $this->appointment->barber?->user->real_name
                    ?: $this->appointment->barber?->user->name
                    ?: 'Our Team',
                'serviceName' => $this->appointment->service->name ?? 'Grooming Service',
                'appointmentDate' => SalonClock::formatDate($this->appointment->appointment_date),
                'appointmentTime' => SalonClock::formatTime($this->appointment->appointment_time),
                'bookingsUrl' => "{$frontendUrl}/customer/dashboard/bookings",
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
