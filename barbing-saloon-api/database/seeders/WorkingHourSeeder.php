<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\WorkingHour;
use Illuminate\Database\Seeder;

class WorkingHourSeeder extends Seeder
{
    public function run(): void
    {
        Barber::all()->each(function (Barber $barber) {
            foreach (range(0, 6) as $day) {
                WorkingHour::updateOrCreate(
                    ['barber_id' => $barber->id, 'day_of_week' => $day],
                    [
                        'open_time' => '08:00:00',
                        'close_time' => '19:00:00',
                        'is_closed' => $day === 0,
                    ]
                );
            }
        });
    }
}