<?php

namespace App\Domain\Identity\Actions;

use App\Models\Barber;
use App\Models\WorkingHour;

class UpdateBarberWorkingHours
{
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

        return $this->workingHours();
    }
}
