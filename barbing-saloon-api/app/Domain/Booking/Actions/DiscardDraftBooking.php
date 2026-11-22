<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiscardDraftBooking
{
    /**
     * Discard an unfinished draft appointment, freeing up the slot immediately.
     */
    public function execute(Appointment $appointment, User $user, string $reason = 'Discarded by customer'): Appointment
    {
        return DB::transaction(function () use ($appointment, $user, $reason) {
            $previousStatus = $appointment->status instanceof AppointmentStatus
                ? $appointment->status->value
                : (string) $appointment->status;

            $appointment->update([
                'status' => AppointmentStatus::cancelled->value,
                'cancellation_reason' => $reason,
                'draft_expires_at' => null,
            ]);

            try {
                AppointmentStatusHistory::create([
                    'appointment_id' => $appointment->id,
                    'previous_status' => $previousStatus,
                    'new_status' => AppointmentStatus::cancelled->value,
                    'changed_by_user_id' => $user->id,
                    'reason' => $reason,
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning("Could not record status history on discard draft: {$e->getMessage()}");
            }

            return $appointment->fresh(['service.category', 'barber.user', 'serviceZone']);
        });
    }
}
