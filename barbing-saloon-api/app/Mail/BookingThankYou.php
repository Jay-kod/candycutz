<?php

declare(strict_types=1);

namespace App\Mail;

use App\Domain\Notification\Services\TransactionalMailRunner;
use App\Domain\Payment\Services\PricingService;
use App\Domain\Shared\Services\SalonClock;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingThankYou extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Appointment $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment->load(['customer', 'barber.user', 'service', 'payment']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thank You for Visiting CandyCutz',
        );
    }

    public function content(): Content
    {
        $pricing = app(PricingService::class);
        $payment = $this->appointment->payment;
        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url', 'http://localhost:5173')), '/');
        $totalNaira = $pricing->resolvedNairaAmount($this->appointment, $payment);

        return new Content(
            view: 'mail.booking-thankyou',
            with: [
                'appointment' => $this->appointment,
                'bookingCode' => $this->appointment->verification_code,
                'customerName' => TransactionalMailRunner::resolveRecipientName($this->appointment),
                'barberName' => $this->appointment->barber?->user->real_name
                    ?: $this->appointment->barber?->user->name
                    ?: 'Our Team',
                'serviceName' => $this->appointment->service->name ?? 'Grooming Service',
                'appointmentDate' => SalonClock::formatDate($this->appointment->appointment_date),
                'appointmentTime' => SalonClock::formatTime($this->appointment->appointment_time),
                'completedAtFormatted' => SalonClock::formatTimestamp($this->appointment->updated_at ?? now()),
                'totalFormatted' => $pricing->formatNaira($totalNaira),
                'paymentReference' => (string) ($payment?->reference ?: $payment?->transaction_ref ?: $this->appointment->verification_code),
                'reviewsUrl' => "{$frontendUrl}/customer/dashboard/reviews?appointment_id={$this->appointment->id}",
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
