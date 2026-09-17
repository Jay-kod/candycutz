<?php

namespace Database\Factories;

use App\Models\Barber;
use App\Models\WorkingHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WorkingHour> */
class WorkingHourFactory extends Factory
{
    protected $model = WorkingHour::class;

    public function definition(): array
    {
        return [
            'barber_id' => Barber::factory(),
            'day_of_week' => fake()->numberBetween(1, 6),
            'open_time' => '08:00:00',
            'close_time' => '19:00:00',
            'is_closed' => false,
        ];
    }
}
