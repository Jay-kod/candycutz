<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Booking\Actions\MarkNoShow;
use App\Domain\Booking\Services\NoShowPolicyService;
use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\Shared\Services\SalonClock;
use App\Jobs\SendNoShowWarningEmail;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AutoMarkNoShowsCommand extends Command
{
    protected $signature = 'bookings:process-no-shows {--dry-run : Inspect candidates without sending warnings or updating appointments}';

    protected $description = 'Send late-arrival warnings (email + web/app push) and auto-mark confirmed appointments past their no-show deadline';

    public function handle(NoShowPolicyService $policyService, MarkNoShow $markNoShow): int
    {
        $startTime = microtime(true);
        $dryRun = (bool) $this->option('dry-run');
        $config = $policyService->getConfig();
        $now = SalonClock::now();

        $minDate = $now->copy()->subDay()->toDateString();
        $maxDate = $now->copy()->addDay()->toDateString();

        /** @var array<int, int> $candidateIds */
        $candidateIds = [];

        Appointment::query()
            ->select(['id'])
            ->where('status', AppointmentStatus::confirmed->value)
            ->whereBetween('appointment_date', [$minDate, $maxDate])
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$candidateIds): void {
                foreach ($rows as $row) {
                    $candidateIds[] = (int) $row->id;
                }
            });

        $warningsSent = 0;
        $markedCount = 0;

        foreach (array_chunk($candidateIds, 100) as $batchIds) {
            $appointments = Appointment::query()
                ->with(['customer', 'barber.user', 'service', 'payment'])
                ->whereIn('id', $batchIds)
                ->get();

            foreach ($appointments as $appointment) {
                $deadline = $policyService->effectiveDeadline($appointment);
                $warningAt = $policyService->warningTime($appointment);

                if ($deadline === null || $warningAt === null) {
                    continue;
                }

                // 1. Auto-mark no-show when effective deadline (including any grace extension) has passed
                if ($config['no_show_auto_mark_enabled'] && $now->greaterThanOrEqualTo($deadline)) {
                    if ($dryRun) {
                        $this->comment("  [DRY-RUN] Would auto-mark no-show for #{$appointment->id} ({$appointment->verification_code})");

                        continue;
                    }

                    try {
                        $markNoShow->execute(
                            appointment: $appointment,
                            actor: null,
                            reason: "Automatically marked no-show after {$config['no_show_delay_minutes']}m arrival window elapsed.",
                            automatic: true
                        );
                        $markedCount++;
                        $this->info("  -> Marked no-show for appointment #{$appointment->id} ({$appointment->verification_code}).");
                    } catch (\Throwable $e) {
                        Log::error("AutoMarkNoShowsCommand failed marking #{$appointment->id}: {$e->getMessage()}");
                    }

                    continue;
                }

                // 2. Dispatch late-arrival warning (email + web/app push) inside the warning window
                if ($appointment->no_show_warning_sent_at === null && $now->greaterThanOrEqualTo($warningAt) && $now->lessThan($deadline)) {
                    if ($dryRun) {
                        $this->comment("  [DRY-RUN] Would send no-show warning for #{$appointment->id} ({$appointment->verification_code})");

                        continue;
                    }

                    try {
                        $claimed = Appointment::query()
                            ->whereKey($appointment->id)
                            ->whereNull('no_show_warning_sent_at')
                            ->update(['no_show_warning_sent_at' => now()]);

                        if ($claimed !== 1) {
                            continue;
                        }

                        $minutesRemaining = max(1, (int) ceil($now->diffInSeconds($deadline, false) / 60));
                        $canExtend = $policyService->canCustomerExtend($appointment, $now)['allowed'];

                        $customerNotification = NotificationDispatcher::noShowWarning($appointment, $minutesRemaining, $canExtend);
                        SendNoShowWarningEmail::dispatch($appointment, $minutesRemaining, $canExtend, $customerNotification?->id);

                        $warningsSent++;
                        $this->info("  -> Dispatched no-show warning for #{$appointment->id} ({$appointment->verification_code}).");
                    } catch (\Throwable $e) {
                        Log::error("AutoMarkNoShowsCommand failed warning #{$appointment->id}: {$e->getMessage()}");
                    }
                }
            }
        }

        $this->recordHeartbeat($warningsSent, $markedCount, $startTime);
        $this->info("No-show sweep complete. Warnings: {$warningsSent}, Marked No-Show: {$markedCount}.");

        return Command::SUCCESS;
    }

    protected function recordHeartbeat(int $warningsSent, int $markedCount, float $startTime): void
    {
        try {
            Cache::put('telemetry:no_shows:last_run', [
                'timestamp' => now()->toIso8601String(),
                'warnings_sent' => $warningsSent,
                'marked_count' => $markedCount,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'status' => 'optimal',
            ], 86400);
        } catch (\Throwable) {
            // Keep resilient if cache store is unavailable
        }
    }
}
