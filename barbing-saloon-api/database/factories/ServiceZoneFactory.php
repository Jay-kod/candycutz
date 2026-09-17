<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\ServiceZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ServiceZone> */
class ServiceZoneFactory extends Factory
{
    protected $model = ServiceZone::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => fake()->unique()->city().' Zone',
            'description' => fake()->sentence(),
            'boundary_polygon' => null,
            'radius_km' => fake()->randomFloat(2, 5, 20),
            'base_travel_fee' => fake()->randomFloat(2, 1000, 5000),
            'per_km_fee' => fake()->randomFloat(2, 50, 300),
            'is_active' => true,
        ];
    }
}
