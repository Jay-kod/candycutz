<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Notification\Services\TransactionalMailRunner;
use App\Mail\BookingReminder;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBookingReminder implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 300;

    public Appointment $appointment;

    public int $minutesBefore;

    public ?int $notificationId;

    public function __construct(Appointment $appointment, int $minutesBefore = 30, ?int $notificationId = null)
    {
        $this->appointment = $appointment;
        $this->minutesBefore = max(1, $minutesBefore);
        $this->notificationId = $notificationId;
    }

    public function uniqueId(): string
    {
        return "booking-reminder:{$this->appointment->id}:{$this->minutesBefore}";
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
            mailable: new BookingReminder($this->appointment, $this->minutesBefore),
            failureLabel: 'booking reminder email',
            notificationId: $this->notificationId,
            respectPreferences: true,
            preferenceType: 'booking_reminder'
        );
    }
}
