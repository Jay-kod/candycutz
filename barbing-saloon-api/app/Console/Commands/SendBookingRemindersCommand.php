<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\Shared\Services\SalonClock;
use App\Jobs\SendBookingReminder;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendBookingRemindersCommand extends Command
{
    protected $signature = 'bookings:send-reminders {--minutes=30 : Lead time in minutes before appointment start} {--dry-run : Inspect due reminders without dispatching}';

    protected $description = 'Send 30-minute pre-booking reminders to customers (in-app + email) and admins (in-app)';

    public function handle(): int
    {
        $startTime = microtime(true);
        $minutes = max(1, (int) $this->option('minutes'));
        $dryRun = (bool) $this->option('dry-run');

        $now = SalonClock::now();
        $windowEnd = $now->copy()->addMinutes($minutes);

        $minDate = $now->copy()->subDay()->toDateString();
        $maxDate = $windowEnd->copy()->addDay()->toDateString();

        // Phase 1: Lightweight indexed scan so historical un-reminded rows can never starve due appointments
        /** @var array<int, int> $dueIds */
        $dueIds = [];

        Appointment::query()
            ->select(['id', 'appointment_date', 'appointment_time'])
            ->whereNull('reminder_30m_sent_at')
            ->whereBetween('appointment_date', [$minDate, $maxDate])
            ->where(function ($query) {
                $query->where('status', AppointmentStatus::confirmed->value)
                    ->orWhere(function ($paidPending) {
                        $paidPending->where('status', AppointmentStatus::pending->value)
                            ->where('deposit_paid', true);
                    });
            })
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$dueIds, $now, $windowEnd): void {
                foreach ($rows as $candidate) {
                    $startAt = SalonClock::parseAppointmentStart($candidate);
                    if ($startAt !== null && $startAt->greaterThan($now) && $startAt->lessThanOrEqualTo($windowEnd)) {
                        $dueIds[] = (int) $candidate->id;
                    }
                }
            });

        if ($dueIds === []) {
            $this->info('No bookings due for 30-minute reminders.');
            $this->recordHeartbeat(0, $startTime);

            return Command::SUCCESS;
        }

        $dueCount = count($dueIds);
        $this->info("Found {$dueCount} appointment(s) due within {$minutes} minute(s).");
        $sentCount = 0;

        // Phase 2: Eager-load relations in bounded batches solely for due appointments
        foreach (array_chunk($dueIds, 100) as $idBatch) {
            $dueAppointments = Appointment::query()
                ->with(['customer', 'barber.user', 'service', 'serviceZone'])
                ->whereIn('id', $idBatch)
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->get();

            foreach ($dueAppointments as $appointment) {
                if ($dryRun) {
                    $this->comment("  [DRY-RUN] Would remind appointment #{$appointment->id} ({$appointment->verification_code})");

                    continue;
                }

                try {
                    // Atomic conditional claim prevents duplicate reminders across overlapping workers
                    $claimed = Appointment::query()
                        ->whereKey($appointment->id)
                        ->whereNull('reminder_30m_sent_at')
                        ->update(['reminder_30m_sent_at' => now()]);

                    if ($claimed !== 1) {
                        continue;
                    }

                    $customerNotification = NotificationDispatcher::bookingUpcomingReminder($appointment, $minutes);
                    SendBookingReminder::dispatch($appointment, $minutes, $customerNotification?->id);

                    $sentCount++;
                    $this->info("  -> Dispatched {$minutes}m reminder for appointment #{$appointment->id} ({$appointment->verification_code}).");
                } catch (\Throwable $e) {
                    Log::error("Failed sending {$minutes}m reminder for appointment #{$appointment->id}: {$e->getMessage()}");
                    $this->error("  -> ERROR for #{$appointment->id}: {$e->getMessage()}");
                }
            }
        }

        $this->recordHeartbeat($sentCount, $startTime);
        $this->info("Reminder sweep complete. Dispatched: {$sentCount}.");

        return Command::SUCCESS;
    }

    protected function recordHeartbeat(int $sentCount, float $startTime): void
    {
        try {
            Cache::put('telemetry:reminders:last_run', [
                'timestamp' => now()->toIso8601String(),
                'sent_count' => $sentCount,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'status' => 'optimal',
            ], 86400);
        } catch (\Throwable) {
            // Keep command resilient if cache store is unavailable
        }
    }
}
