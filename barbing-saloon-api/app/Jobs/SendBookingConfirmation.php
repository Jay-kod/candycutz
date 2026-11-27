<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Notification\Services\TransactionalMailRunner;
use App\Mail\BookingConfirmation;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBookingConfirmation implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 300;

    public Appointment $appointment;

    public ?Payment $payment;

    public ?int $notificationId;

    public function __construct(Appointment $appointment, ?Payment $payment = null, ?int $notificationId = null)
    {
        $this->appointment = $appointment;
        $this->payment = $payment;
        $this->notificationId = $notificationId;
    }

    public function uniqueId(): string
    {
        return 'booking-confirmation:'.$this->appointment->id;
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [15, 60, 180];
    }

    public function handle(): void
    {
        app(TransactionalMailRunner::class)->send(
            appointment: $this->appointment,
            mailable: new BookingConfirmation($this->appointment, $this->payment),
            failureLabel: 'booking confirmation & receipt email',
            notificationId: $this->notificationId,
            respectPreferences: false,
            preferenceType: 'payment_verified'
        );
    }
}
