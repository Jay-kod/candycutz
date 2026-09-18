<?php

namespace App\Domain\Identity\Actions;

use App\Models\Barber;
use App\Models\WorkingHour;

class UpdateBarberWorkingHours
{
    /**
     * @param  array<int, array<string, mixed>>  $hours
     * @return array<int, array<string, mixed>>
     */
    public function execute(int $barberId, array $hours): array
    {
        $barber = Barber::findOrFail($barberId);
        foreach ($hours as $h) {
            if (! isset($h['day_of_week'])) {
                continue;
            }

            WorkingHour::updateOrCreate(
                ['barber_id' => $barber->id, 'day_of_week' => (int) $h['day_of_week']],
                [
                    'open_time' => $h['open_time'] ?? '09:00:00',
                    'close_time' => $h['close_time'] ?? '18:00:00',
                    'is_closed' => isset($h['is_closed']) ? (bool) $h['is_closed'] : false,
                ]
            );
        }

        return $barber->workingHours->map(fn ($h) => [
            'id' => $h->id,
            'day_of_week' => $h->day_of_week,
            'open_time' => substr($h->open_time, 0, 5),
            'close_time' => substr($h->close_time, 0, 5),
            'is_closed' => (bool) $h->is_closed,
        ])->values()->all();
    }
}
