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

class BookingReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Appointment $appointment;

    public int $minutesBefore;

    public function __construct(Appointment $appointment, int $minutesBefore = 30)
    {
        $this->appointment = $appointment->load(['customer', 'barber.user', 'service', 'serviceZone']);
        $this->minutesBefore = max(1, $minutesBefore);
    }

    public function envelope(): Envelope
    {
        $bookingCode = $this->appointment->verification_code;
        $subject = $this->minutesBefore <= 60
            ? "Reminder: Your CandyCutz Appointment Starts in {$this->minutesBefore} Minutes ({$bookingCode})"
            : "Reminder: Your CandyCutz Appointment is Coming Up ({$bookingCode})";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url', 'http://localhost:5173')), '/');

        return new Content(
            view: 'mail.booking-reminder',
            with: [
                'appointment' => $this->appointment,
                'minutesBefore' => $this->minutesBefore,
                'bookingCode' => $this->appointment->verification_code,
                'customerName' => TransactionalMailRunner::resolveRecipientName($this->appointment),
                'barberName' => $this->appointment->barber?->user->real_name
                    ?: $this->appointment->barber?->user->name
                    ?: 'Our Team',
                'serviceName' => $this->appointment->service->name ?? 'Grooming Service',
                'appointmentDate' => SalonClock::formatDate($this->appointment->appointment_date),
                'appointmentTime' => SalonClock::formatTime($this->appointment->appointment_time),
                'appointmentTypeLabel' => $this->appointment->appointment_type === 'home_service'
                    ? ('Home Service'.($this->appointment->serviceZone ? " ({$this->appointment->serviceZone->name})" : ''))
                    : 'In-Shop · Keffi Main Hub',
                'bookingsUrl' => "{$frontendUrl}/customer/dashboard/bookings",
                'codesUrl' => "{$frontendUrl}/customer/dashboard/my-codes",
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
