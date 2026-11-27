<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ReleaseUnpaidBookingsCommand extends Command
{
    protected $signature = 'bookings:release-unpaid {--minutes=30 : Window in minutes before unpaid bookings expire} {--dry-run : Only inspect without updating}';

    protected $description = 'Release slot holds and cancel pending appointments whose payment window expired';

    public function handle(): int
    {
        $startTime = microtime(true);
        $minutes = (int) $this->option('minutes');
        $dryRun = (bool) $this->option('dry-run');

        $cutoff = now()->subMinutes($minutes);

        // 1. Pending bookings whose payment window expired
        $expiredAppointments = Appointment::where('status', AppointmentStatus::pending->value)
            ->where(function ($q) use ($cutoff) {
                $q->where(function ($sub) use ($cutoff) {
                    $sub->whereNull('payment_due_at')
                        ->where('created_at', '<=', $cutoff);
                })->orWhere(function ($sub) {
                    $sub->whereNotNull('payment_due_at')
                        ->where('payment_due_at', '<=', now());
                });
            })
            ->where(function ($q) {
                $q->where('payment_status', AppointmentPaymentStatus::unpaid->value)
                    ->orWhere('payment_status', 'unpaid');
            })
            ->where('deposit_paid', false)
            ->limit(100)
            ->get();

        // 2. Draft bookings whose 15-minute hold expired
        $expiredDrafts = Appointment::where('status', AppointmentStatus::draft->value)
            ->whereNotNull('draft_expires_at')
            ->where('draft_expires_at', '<=', now())
            ->limit(250)
            ->get();

        if ($expiredAppointments->isEmpty() && $expiredDrafts->isEmpty()) {
            $this->info('No expired unpaid bookings or drafts found.');

            Cache::put('telemetry:sweeper:last_run', [
                'timestamp' => now()->toIso8601String(),
                'cancelled_count' => 0,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'status' => 'optimal',
            ], 86400);

            return Command::SUCCESS;
        }

        $this->info("Found {$expiredAppointments->count()} expired unpaid booking(s) and {$expiredDrafts->count()} expired draft(s).");

        $cancelled = 0;

        foreach ($expiredAppointments as $appointment) {
            $this->line("Releasing appointment #{$appointment->id} [ref: {$appointment->booking_reference}]...");

            if ($dryRun) {
                $this->comment("  [DRY-RUN] Would cancel appointment #{$appointment->id}");

                continue;
            }

            try {
                $previousStatus = $appointment->status->value ?? $appointment->status;

                $appointment->update([
                    'status' => AppointmentStatus::cancelled->value,
                    'cancellation_reason' => 'Payment window expired without completion.',
                ]);

                try {
                    AppointmentStatusHistory::create([
                        'appointment_id' => $appointment->id,
                        'previous_status' => $previousStatus,
                        'new_status' => AppointmentStatus::cancelled->value,
                        'changed_by_user_id' => null,
                        'reason' => 'Automated release: payment window expired.',
                        'created_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("Could not create status history on release: {$e->getMessage()}");
                }

                $this->info("  -> CANCELLED appointment #{$appointment->id} and released slot.");
                $cancelled++;
            } catch (\Throwable $e) {
                Log::error("Failed releasing appointment #{$appointment->id}: {$e->getMessage()}");
                $this->error("  -> ERROR: {$e->getMessage()}");
            }
        }

        foreach ($expiredDrafts as $draft) {
            $this->line("Releasing expired draft #{$draft->id} [ref: {$draft->booking_reference}]...");

            if ($dryRun) {
                $this->comment("  [DRY-RUN] Would cancel draft #{$draft->id}");

                continue;
            }

            try {
                $previousStatus = $draft->status->value ?? $draft->status;

                $draft->update([
                    'status' => AppointmentStatus::cancelled->value,
                    'cancellation_reason' => 'Draft slot hold expired without payment.',
                    'draft_expires_at' => null,
                ]);

                try {
                    AppointmentStatusHistory::create([
                        'appointment_id' => $draft->id,
                        'previous_status' => $previousStatus,
                        'new_status' => AppointmentStatus::cancelled->value,
                        'changed_by_user_id' => null,
                        'reason' => 'Automated release: draft hold TTL expired.',
                        'created_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("Could not create status history on draft release: {$e->getMessage()}");
                }

                $this->info("  -> CANCELLED draft #{$draft->id} and released slot.");
                $cancelled++;
            } catch (\Throwable $e) {
                Log::error("Failed releasing draft #{$draft->id}: {$e->getMessage()}");
                $this->error("  -> ERROR: {$e->getMessage()}");
            }
        }

        Cache::put('telemetry:sweeper:last_run', [
            'timestamp' => now()->toIso8601String(),
            'cancelled_count' => $cancelled,
            'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            'status' => 'optimal',
        ], 86400);

        $this->info("Release complete. Cancelled: {$cancelled} total booking(s)/draft(s).");

        return Command::SUCCESS;
    }
}
