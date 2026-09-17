<?php

namespace Database\Factories;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Holiday> */
class HolidayFactory extends Factory
{
    protected $model = Holiday::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'date' => fake()->dateTimeBetween('+1 day', '+1 year'),
            'is_recurring' => false,
        ];
    }
}
