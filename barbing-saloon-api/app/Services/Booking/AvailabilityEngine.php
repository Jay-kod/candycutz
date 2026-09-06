<?php

declare(strict_types=1);

namespace App\Services\Booking;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BarberAvailability;
use App\Models\BlockedPeriod;
use App\Models\Branch;
use App\Models\BusinessHour;
use App\Models\Holiday;
use Carbon\Carbon;

class AvailabilityEngine
{
    /**
     * Calculate available starting time slots for a given date, barber, duration, and appointment mode.
     *
     * @return array<string> Array of times in 'H:i' format.
     */
    public function calculateAvailableSlots(
        Carbon $date,
        int $barberId,
        int $totalDurationMinutes,
        bool $isHomeService = false,
        ?int $branchId = null
    ): array {
        // 1. Verify date is not in the past
        if ($date->isPast() && !$date->isToday()) {
            return [];
        }

        // 2. Check branch holidays
        $hasBranchHoliday = Holiday::where('date', $date->toDateString())
            ->where(function ($q) use ($branchId, $barberId) {
                if ($branchId) $q->where('branch_id', $branchId);
                $q->orWhere('barber_id', $barberId);
            })
            ->exists();

        if ($hasBranchHoliday) {
            return [];
        }

        // 3. Resolve Barber working hours for this day of week (0 = Sunday ... 6 = Saturday)
        $schedule = BarberAvailability::where('barber_id', $barberId)
            ->where('day_of_week', $date->dayOfWeek)
            ->where('is_available', true)
            ->first();

        if (!$schedule) {
            return [];
        }

        // 4. Calculate working window bounds
        $windowStart = $date->copy()->setTimeFromTimeString($schedule->start_time);
        $windowEnd = $date->copy()->setTimeFromTimeString($schedule->end_time);

        // If booking for today, advance window start past current time + 30 min minimum notice
        if ($date->isToday()) {
            $earliestPossible = now()->addMinutes(30);
            if ($earliestPossible->gt($windowStart)) {
                $windowStart = $earliestPossible;
            }
        }

        // 5. Account for home service travel buffer (30 minutes buffer before and after)
        $effectiveDuration = $isHomeService ? ($totalDurationMinutes + 30) : $totalDurationMinutes;

        // 6. Fetch all conflicting active appointments for this barber on this date
        $existingAppointments = Appointment::where('barber_id', $barberId)
            ->where('appointment_date', $date->toDateString())
            ->whereNotIn('status', ['cancelled'])
            ->get(['start_time', 'end_time', 'appointment_type']);

        // 7. Fetch all blocked periods (lunch, prayer, personal leave)
        $blockedPeriods = BlockedPeriod::where('barber_id', $barberId)
            ->whereDate('start_datetime', $date->toDateString())
            ->get(['start_datetime', 'end_datetime']);

        $availableSlots = [];
        $slotStepMinutes = 30;
        $cursor = $windowStart->copy();

        // Round cursor up to nearest 30-minute interval
        $minute = (int) $cursor->minute;
        if ($minute > 0 && $minute < 30) {
            $cursor->minute(30)->second(0);
        } elseif ($minute > 30) {
            $cursor->addHour()->minute(0)->second(0);
        }

        while ($cursor->copy()->addMinutes($effectiveDuration)->lte($windowEnd)) {
            $slotEnd = $cursor->copy()->addMinutes($effectiveDuration);
            $candidateStartTime = $cursor->format('H:i:s');
            $candidateEndTime = $slotEnd->format('H:i:s');

            $hasConflict = false;

            // Check overlap against existing appointments
            foreach ($existingAppointments as $app) {
                $appStart = (string) $app->start_time;
                $appEnd = (string) ($app->end_time ?: Carbon::parse($app->start_time)->addMinutes(30)->toTimeString());

                // Home service travel buffer padding
                if ($app->appointment_type === 'home_service') {
                    $appEnd = Carbon::parse($appEnd)->addMinutes(30)->toTimeString();
                }

                if ($candidateStartTime < $appEnd && $candidateEndTime > $appStart) {
                    $hasConflict = true;
                    break;
                }
            }

            // Check overlap against blocked periods
            if (!$hasConflict) {
                foreach ($blockedPeriods as $block) {
                    $blockStart = Carbon::parse($block->start_datetime)->toTimeString();
                    $blockEnd = Carbon::parse($block->end_datetime)->toTimeString();

                    if ($candidateStartTime < $blockEnd && $candidateEndTime > $blockStart) {
                        $hasConflict = true;
                        break;
                    }
                }
            }

            if (!$hasConflict) {
                $availableSlots[] = $cursor->format('H:i');
            }

            $cursor->addMinutes($slotStepMinutes);
        }

        return $availableSlots;
    }
}
