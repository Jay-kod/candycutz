<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Notification\Services\TransactionalMailRunner;
use App\Mail\NoShowWarning;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNoShowWarningEmail implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 300;

    public Appointment $appointment;

    public int $minutesRemaining;

    public bool $canExtend;

    public ?int $notificationId;

    public function __construct(
        Appointment $appointment,
        int $minutesRemaining = 5,
        bool $canExtend = true,
        ?int $notificationId = null
    ) {
        $this->appointment = $appointment;
        $this->minutesRemaining = max(1, $minutesRemaining);
        $this->canExtend = $canExtend;
        $this->notificationId = $notificationId;
    }

    public function uniqueId(): string
    {
        $extensionCount = (int) ($this->appointment->grace_extension_count ?? 0);

        return "no-show-warning:{$this->appointment->id}:ext{$extensionCount}";
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
            mailable: new NoShowWarning($this->appointment, $this->minutesRemaining, $this->canExtend),
            failureLabel: 'no-show warning email',
            notificationId: $this->notificationId,
            respectPreferences: false,
            preferenceType: 'booking_no_show_warning'
        );
    }
}
