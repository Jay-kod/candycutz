<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReleaseUnpaidBookingsCommand extends Command
{
    protected $signature = 'bookings:release-unpaid {--minutes=30 : Window in minutes before unpaid bookings expire} {--dry-run : Only inspect without updating}';

    protected $description = 'Release slot holds and cancel pending appointments whose payment window expired';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $dryRun = (bool) $this->option('dry-run');

        $cutoff = now()->subMinutes($minutes);

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
            ->get();

        if ($expiredAppointments->isEmpty()) {
            $this->info('No expired unpaid bookings found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$expiredAppointments->count()} expired unpaid booking(s).");

        $cancelled = 0;

        foreach ($expiredAppointments as $appointment) {
            $this->line("Releasing appointment #{$appointment->id} [ref: {$appointment->booking_reference}]...");

            if ($dryRun) {
                $this->comment("  [DRY-RUN] Would cancel appointment #{$appointment->id}");

                continue;
            }

            try {
                $previousStatus = $appointment->status?->value ?? $appointment->status;

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

        $this->info("Release complete. Cancelled: {$cancelled} appointment(s).");

        return Command::SUCCESS;
    }
}
