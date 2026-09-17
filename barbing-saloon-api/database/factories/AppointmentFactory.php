<?php

namespace Database\Factories;

use App\Core\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Branch;
use App\Models\Service;
use App\Models\ServiceZone;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Appointment> */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $date = fake()->dateTimeBetween('+1 day', '+30 days');
        $start = fake()->dateTimeBetween('08:00', '17:00');
        $end = (clone $start)->modify('+45 minutes');
        $price = fake()->randomFloat(2, 1000, 15000);

        return [
            'booking_reference' => 'BK-' . strtoupper(fake()->unique()->bothify('####??')),
            'branch_id' => Branch::factory(),
            'customer_id' => User::factory()->customer(),
            'barber_id' => Barber::factory(),
            'service_id' => Service::factory(),
            'appointment_type' => 'in_shop',
            'service_zone_id' => null,
            'customer_address_id' => null,
            'appointment_date' => $date->format('Y-m-d'),
            'appointment_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'total_duration_minutes' => 45,
            'total_amount' => $price,
            'travel_fee' => 0,
            'tip_amount' => 0,
            'discount_amount' => 0,
            'grand_total' => $price,
            'status' => AppointmentStatus::pending,
            'cancellation_reason' => null,
            'notes' => fake()->sentence(),
            'client_name' => fake()->name(),
            'client_phone' => fake()->numerify('080########'),
            'client_email' => fake()->safeEmail(),
            'total_price' => $price,
            'deposit_paid' => false,
            'deposit_amount' => 0,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => AppointmentStatus::pending]);
    }

    public function confirmed(): static
    {
        return $this->state(['status' => AppointmentStatus::confirmed]);
    }

    public function completed(): static
    {
        return $this->state(['status' => AppointmentStatus::completed]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => AppointmentStatus::cancelled]);
    }

    public function walkIn(): static
    {
        return $this->state([
            'customer_id' => null,
            'appointment_type' => 'in_shop',
        ]);
    }
}
