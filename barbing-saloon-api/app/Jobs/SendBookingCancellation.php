<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Notification\Services\TransactionalMailRunner;
use App\Mail\BookingCancellation;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBookingCancellation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 15;

    public Appointment $appointment;

    public ?int $notificationId;

    public function __construct(Appointment $appointment, ?int $notificationId = null)
    {
        $this->appointment = $appointment;
        $this->notificationId = $notificationId;
    }

    public function handle(): void
    {
        app(TransactionalMailRunner::class)->send(
            appointment: $this->appointment,
            mailable: new BookingCancellation($this->appointment),
            failureLabel: 'booking cancellation email',
            notificationId: $this->notificationId,
            respectPreferences: false,
            preferenceType: 'booking_cancelled'
        );
    }
}
