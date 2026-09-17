<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AppointmentItem> */
class AppointmentItemFactory extends Factory
{
    protected $model = AppointmentItem::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'service_id' => Service::factory(),
            'price' => fake()->randomFloat(2, 1000, 15000),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
            'created_at' => now(),
        ];
    }
}
