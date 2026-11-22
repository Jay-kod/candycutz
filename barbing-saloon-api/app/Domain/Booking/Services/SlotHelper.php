<?php

namespace App\Domain\Booking\Services;

use App\Models\Barber;
use App\Models\BlockedPeriod;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\Setting;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class SlotHelper
{
    /**
     * @return array<int, string>
     */
    public function generate(Carbon $date, Barber $barber, Service $service): array
    {
        if ($this->isHoliday($date)) {
            return [];
        }

        $dayOfWeek = (int) $date->dayOfWeekIso % 7;
        $workingHour = $barber->workingHours()->where('day_of_week', $dayOfWeek)->first();

        if ($workingHour) {
            if ($workingHour->is_closed) {
                return [];
            }
            $openTime = $workingHour->open_time;
            $closeTime = $workingHour->close_time;
        } else {
            // Sunday closed by default; otherwise standard 08:00 to 19:00
            if ($dayOfWeek === 0) {
                return [];
            }
            $openTime = '08:00:00';
            $closeTime = '19:00:00';
        }

        $intervalMinutes = (int) $this->setting('slot_interval_minutes', 30);
        $minNoticeHours = (int) $this->setting('min_notice_hours', 2);

        $start = Carbon::parse($date->toDateString().' '.$openTime);
        $end = Carbon::parse($date->toDateString().' '.$closeTime);
        $period = CarbonPeriod::create($start, "{$intervalMinutes} minutes", $end->copy()->subMinutes($service->duration_minutes));

        $confirmedAppointments = $barber->appointments()
            ->where('appointment_date', $date->toDateString())
            ->where(function ($q) {
                $q->whereIn('status', ['pending', 'confirmed', 'checked_in', 'in_progress'])
                    ->orWhere(function ($draftQ) {
                        $draftQ->where('status', 'draft')
                            ->where(function ($tq) {
                                $tq->whereNull('draft_expires_at')
                                    ->orWhere('draft_expires_at', '>', now());
                            });
                    });
            })
            ->with('service')
            ->get();

        $blockedPeriods = BlockedPeriod::where('barber_id', $barber->id)
            ->whereDate('start_datetime', $date->toDateString())
            ->get();

        $now = now();
        $slots = [];

        foreach ($period as $slotStart) {
            $slotEnd = $slotStart->copy()->addMinutes($service->duration_minutes);

            if ($date->isToday()) {
                if ($slotStart->lessThan($now)) {
                    continue;
                }

                if ($slotStart->lessThan($now->copy()->addHours($minNoticeHours))) {
                    continue;
                }
            }

            $isBlocked = $blockedPeriods->contains(function ($blocked) use ($slotStart, $slotEnd) {
                return $slotStart->lt($blocked->end_datetime) && $slotEnd->gt($blocked->start_datetime);
            });

            if ($isBlocked) {
                continue;
            }

            $overlaps = $confirmedAppointments->contains(function ($appointment) use ($slotStart, $slotEnd) {
                $appointmentDate = Carbon::parse($appointment->appointment_date)->toDateString();
                $appointmentStart = Carbon::parse($appointmentDate.' '.$appointment->appointment_time);
                $appointmentEnd = $appointment->end_time
                    ? Carbon::parse($appointmentDate.' '.$appointment->end_time)
                    : $appointmentStart->copy()->addMinutes($appointment->service->duration_minutes ?? 30);

                return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
            });

            if (! $overlaps) {
                $slots[] = $slotStart->format('H:i');
            }
        }

        return $slots;
    }

    protected function isHoliday(Carbon $date): bool
    {
        return Holiday::query()->whereDate('date', $date->toDateString())->exists();
    }

    protected function setting(string $key, mixed $default = null): mixed
    {
        return \Illuminate\Support\Facades\Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            return Setting::query()->where('key', $key)->value('value') ?? $default;
        });
    }
}
