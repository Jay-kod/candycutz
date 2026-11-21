<?php

namespace Database\Factories;

use App\Models\Barber;
use App\Models\BlockedPeriod;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BlockedPeriod> */
class BlockedPeriodFactory extends Factory
{
    protected $model = BlockedPeriod::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+14 days');

        return [
            'barber_id' => Barber::factory(),
            'branch_id' => Branch::factory(),
            'start_datetime' => $start,
            'end_datetime' => (clone $start)->modify('+2 hours'),
            'reason' => fake()->sentence(),
            'created_by' => User::factory()->admin(),
        ];
    }
}
