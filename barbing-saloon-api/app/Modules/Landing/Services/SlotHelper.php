<?php

namespace App\Modules\Landing\Services;

use App\Models\Barber;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\Setting;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class SlotHelper
{
    public function generate(Carbon $date, Barber $barber, Service $service): array
    {
        if ($this->isHoliday($date)) {
            return [];
        }

        $workingHour = $barber->workingHours()->where('day_of_week', $date->dayOfWeekIso % 7)->first();

        if (! $workingHour || $workingHour->is_closed) {
            return [];
        }

        $intervalMinutes = (int) $this->setting('slot_interval_minutes', 30);
        $minNoticeHours = (int) $this->setting('min_notice_hours', 2);

        $start = Carbon::parse($date->toDateString().' '.$workingHour->open_time);
        $end = Carbon::parse($date->toDateString().' '.$workingHour->close_time);
        $period = CarbonPeriod::create($start, "{$intervalMinutes} minutes", $end->copy()->subMinutes($service->duration_minutes));

        $confirmedAppointments = $barber->appointments()
            ->whereDate('appointment_date', $date->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('service')
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

            $overlaps = $confirmedAppointments->contains(function ($appointment) use ($slotStart, $slotEnd) {
                $appointmentStart = Carbon::parse($appointment->appointment_date->toDateString().' '.$appointment->appointment_time);
                $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->service->duration_minutes);

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
        return Setting::query()->where('key', $key)->value('value') ?? $default;
    }
}