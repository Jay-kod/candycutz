<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Exceptions\BookingSlotUnavailableException;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ResumeDraftBooking
{
    /**
     * Resume a draft booking: verify slot availability and renew the 15-minute hold.
     *
     * @throws BookingSlotUnavailableException
     */
    public function execute(Appointment $appointment, User $user): Appointment
    {
        return DB::transaction(function () use ($appointment) {
            $appointment->refresh();

            $statusVal = $appointment->status instanceof AppointmentStatus
                ? $appointment->status->value
                : (string) $appointment->status;

            $canResume = ($statusVal === AppointmentStatus::draft->value || $statusVal === 'draft')
                || ($statusVal === AppointmentStatus::cancelled->value && str_contains(strtolower((string) $appointment->cancellation_reason), 'expire'));

            if (! $canResume) {
                return $appointment;
            }

            // Lock barber row for concurrent serialization
            if ($appointment->barber_id) {
                \App\Models\Barber::whereKey($appointment->barber_id)->lockForUpdate()->first();
            }

            $date = Carbon::parse($appointment->appointment_date)->toDateString();
            $startTime = substr(trim((string) $appointment->appointment_time), 0, 5);
            $endTime = $appointment->end_time
                ? substr(trim((string) $appointment->end_time), 0, 5)
                : Carbon::parse("{$date} {$startTime}")->addMinutes($appointment->total_duration_minutes ?: 30)->format('H:i');

            // If the draft's hold expired or was released, verify no other appointment has taken the slot
            $isExpired = ($statusVal === AppointmentStatus::cancelled->value)
                || ($appointment->draft_expires_at !== null && $appointment->draft_expires_at->isPast());

            if ($isExpired) {
                // Clear any other stale expired drafts for this barber/date before conflict check
                Appointment::where('barber_id', $appointment->barber_id)
                    ->where('id', '!=', $appointment->id)
                    ->where('appointment_date', $date)
                    ->where('status', AppointmentStatus::draft->value)
                    ->whereNotNull('draft_expires_at')
                    ->where('draft_expires_at', '<=', now())
                    ->update([
                        'status' => AppointmentStatus::cancelled->value,
                        'cancellation_reason' => 'Draft slot hold expired without payment.',
                        'draft_expires_at' => null,
                    ]);

                $conflict = Appointment::where('barber_id', $appointment->barber_id)
                    ->where('id', '!=', $appointment->id)
                    ->where('appointment_date', $date)
                    ->whereNotIn('status', [AppointmentStatus::cancelled->value, 'cancelled'])
                    ->where(function ($q) {
                        $q->whereNotIn('status', [AppointmentStatus::draft->value, 'draft'])
                            ->orWhere(function ($draftQuery) {
                                $draftQuery->whereIn('status', [AppointmentStatus::draft->value, 'draft'])
                                    ->where(function ($tq) {
                                        $tq->whereNull('draft_expires_at')
                                            ->orWhere('draft_expires_at', '>', now());
                                    });
                            });
                    })
                    ->where(function ($query) use ($startTime, $endTime) {
                        $query->where('appointment_time', '<', $endTime)
                            ->where(function ($q) use ($startTime) {
                                $q->where('end_time', '>', $startTime)
                                    ->orWhereNull('end_time');
                            });
                    })
                    ->lockForUpdate()
                    ->first();

                if ($conflict) {
                    throw new BookingSlotUnavailableException(
                        'Your reserved time slot has expired and was taken by another customer. Please choose a new time.',
                        "{$date} {$startTime}"
                    );
                }
            }

            // Extend hold by 15 minutes from now and ensure draft status
            $appointment->update([
                'status' => AppointmentStatus::draft->value,
                'cancellation_reason' => null,
                'draft_expires_at' => now()->addMinutes(15),
                'draft_step' => 'payment',
            ]);

            return $appointment->fresh(['service.category', 'barber.user', 'serviceZone']);
        });
    }
}
