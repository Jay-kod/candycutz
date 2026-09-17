<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AppointmentStatusHistory> */
class AppointmentStatusHistoryFactory extends Factory
{
    protected $model = AppointmentStatusHistory::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'previous_status' => 'pending',
            'new_status' => 'confirmed',
            'changed_by_user_id' => User::factory()->admin(),
            'reason' => fake()->sentence(),
            'created_at' => now(),
        ];
    }
}
