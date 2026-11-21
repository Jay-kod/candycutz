<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Models\Barber;
use App\Models\WorkingHour;

class UpdateBarberSchedule
{
    /**
     * @param  array<int, array<string, mixed>>  $hours
     */
    public function execute(Barber $barber, array $hours): void
    {
        foreach ($hours as $item) {
            if (! isset($item['day_of_week'])) {
                continue;
            }

            $open = $item['open_time'] ?? ($item['start_time'] ?? '08:00');
            $close = $item['close_time'] ?? ($item['end_time'] ?? '20:00');
            $isClosed = isset($item['is_closed'])
                ? (bool) $item['is_closed']
                : (isset($item['is_off']) ? (bool) $item['is_off'] : false);

            WorkingHour::updateOrCreate(
                [
                    'barber_id' => $barber->id,
                    'day_of_week' => (int) $item['day_of_week'],
                ],
                [
                    'open_time' => $open,
                    'close_time' => $close,
                    'is_closed' => $isClosed,
                ]
            );
        }
    }
}
