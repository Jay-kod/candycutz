<?php

declare(strict_types=1);

namespace App\Mail;

use App\Domain\Notification\Services\TransactionalMailRunner;
use App\Domain\Payment\Services\PricingService;
use App\Domain\Shared\Services\SalonClock;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Appointment $appointment;

    public ?Payment $payment;

    public function __construct(Appointment $appointment, ?Payment $payment = null)
    {
        $this->appointment = $appointment->load(['customer', 'barber.user', 'service', 'serviceZone', 'payment']);
        $this->payment = $payment ?? $this->appointment->payment;
    }

    public function envelope(): Envelope
    {
        $bookingCode = $this->appointment->verification_code;
        $subject = $this->isPaid()
            ? "Your CandyCutz Official Receipt & Booking Code ({$bookingCode})"
            : 'Your CandyCutz Booking is Confirmed';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $pricing = app(PricingService::class);
        $payment = $this->payment ?? $this->appointment->payment;
        $isPaid = $this->isPaid();
        $bookingCode = $this->appointment->verification_code;
        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url', 'http://localhost:5173')), '/');

        $totalNaira = $pricing->resolvedNairaAmount($this->appointment, $payment);
        $travelFeeRaw = (float) ($this->appointment->travel_fee ?? 0);
        $travelFeeNaira = $travelFeeRaw >= 100000 ? ($travelFeeRaw / 100) : $travelFeeRaw;
        $discountRaw = (float) ($this->appointment->discount_amount ?? 0);
        $discountNaira = $discountRaw >= 100000 ? ($discountRaw / 100) : $discountRaw;
        $subtotalNaira = max(0, $totalNaira - $travelFeeNaira + $discountNaira);

        $paymentMethod = (string) ($payment?->channel ?: $payment?->payment_method ?: ($this->appointment->appointment_type === 'walk_in' ? 'pay_at_venue' : 'paystack'));
        $paymentMethodLabel = match (strtolower($paymentMethod)) {
            'card', 'paystack' => 'Paystack · Card / Online',
            'bank_transfer', 'transfer' => 'Bank Transfer',
            'manual_transfer' => 'Verified Bank Transfer',
            'ussd' => 'Bank USSD',
            'bank_account' => 'Bank Account',
            'nqr' => 'NQR Scan',
            'pay_at_venue', 'venue', 'cash' => 'Paid at Venue',
            default => ucwords(str_replace('_', ' ', $paymentMethod)),
        };

        $paidTimestamp = ($payment !== null ? ($payment->paid_at ?? $payment->verified_at) : null)
            ?? $this->appointment->updated_at
            ?? now();
        $receiptNumber = 'RCP-'.str_pad((string) ($payment !== null ? $payment->id : $this->appointment->id), 6, '0', STR_PAD_LEFT);
        $paymentReference = (string) ($payment?->reference ?: $payment?->transaction_ref ?: $bookingCode);

        $dateStr = SalonClock::formatDate($this->appointment->appointment_date);
        $timeStr = SalonClock::formatTime($this->appointment->appointment_time);

        return new Content(
            view: 'mail.booking-confirmation',
            with: [
                'appointment' => $this->appointment,
                'payment' => $payment,
                'isPaid' => $isPaid,
                'bookingCode' => $bookingCode,
                'receiptNumber' => $receiptNumber,
                'paymentReference' => $paymentReference,
                'paymentMethodLabel' => $paymentMethodLabel,
                'paidAtFormatted' => SalonClock::formatTimestamp($paidTimestamp),
                'customerName' => TransactionalMailRunner::resolveRecipientName($this->appointment),
                'customerEmail' => TransactionalMailRunner::resolveRecipientEmail($this->appointment) ?? '',
                'barberName' => $this->appointment->barber?->user->real_name
                    ?: $this->appointment->barber?->user->name
                    ?: 'Master Stylist',
                'serviceName' => $this->appointment->service->name ?? 'Grooming Service',
                'serviceDuration' => (int) ($this->appointment->total_duration_minutes ?: $this->appointment->service?->duration_minutes ?: 30),
                'appointmentTypeLabel' => $this->appointment->appointment_type === 'home_service'
                    ? ('Home Service'.($this->appointment->serviceZone ? " ({$this->appointment->serviceZone->name})" : ''))
                    : 'In-Shop · Keffi Main Hub',
                'appointmentDate' => $dateStr,
                'appointmentTime' => $timeStr,
                'subtotalFormatted' => $pricing->formatNaira($subtotalNaira),
                'travelFeeFormatted' => $travelFeeNaira > 0 ? $pricing->formatNaira($travelFeeNaira) : null,
                'discountFormatted' => $discountNaira > 0 ? $pricing->formatNaira($discountNaira) : null,
                'totalFormatted' => $pricing->formatNaira($totalNaira),
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

    protected function isPaid(): bool
    {
        $payment = $this->payment ?? $this->appointment->payment;

        return (bool) ($this->appointment->deposit_paid
            || ($payment && $payment->status->isSuccess()));
    }
}
