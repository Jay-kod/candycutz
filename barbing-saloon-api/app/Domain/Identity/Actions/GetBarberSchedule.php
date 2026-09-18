<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Models\Barber;
use App\Models\WorkingHour;

class GetBarberSchedule
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(Barber $barber): array
    {
        $hours = WorkingHour::where('barber_id', $barber->id)
            ->orderBy('day_of_week')
            ->get();

        // Ensure all 7 days (0 to 6) are represented
        $schedule = [];
        $existing = $hours->keyBy('day_of_week');
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        for ($d = 0; $d < 7; $d++) {
            if ($existing->has($d)) {
                $row = $existing->get($d);
                $schedule[] = [
                    'id' => $row->id,
                    'day_of_week' => $d,
                    'day_name' => $dayNames[$d],
                    'open_time' => substr((string) $row->open_time, 0, 5),
                    'close_time' => substr((string) $row->close_time, 0, 5),
                    'start_time' => substr((string) $row->open_time, 0, 5),
                    'end_time' => substr((string) $row->close_time, 0, 5),
                    'is_closed' => (bool) $row->is_closed,
                    'is_off' => (bool) $row->is_closed,
                ];
            } else {
                $schedule[] = [
                    'id' => null,
                    'day_of_week' => $d,
                    'day_name' => $dayNames[$d],
                    'open_time' => '08:00',
                    'close_time' => '20:00',
                    'start_time' => '08:00',
                    'end_time' => '20:00',
                    'is_closed' => $d === 0,
                    'is_off' => $d === 0,
                ];
            }
        }

        return $schedule;
    }
}
