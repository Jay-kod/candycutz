<?php

namespace App\Domain\Identity\Actions;

use App\Models\Barber;

class GetWorkingHours
{
    public function execute(): array
    {
        $barbers = Barber::with(['user', 'workingHours' => fn ($q) => $q->orderBy('day_of_week')])->get();

        return $barbers->map(function ($b) {
            return [
                'barber_id' => $b->id,
                'barber_name' => $b->user?->name ?? 'Barber #'.$b->id,
                'hours' => $b->workingHours->map(fn ($h) => [
                    'id' => $h->id,
                    'day_of_week' => $h->day_of_week,
                    'open_time' => substr($h->open_time, 0, 5),
                    'close_time' => substr($h->close_time, 0, 5),
                    'is_closed' => (bool) $h->is_closed,
                ])->values()->all(),
            ];
        })->values()->all();
    }
}
