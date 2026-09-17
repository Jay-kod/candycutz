<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Branch> */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        $name = fake()->city() . ' Hub';

        return [
            'business_id' => Business::factory(),
            'name' => $name,
            'slug' => fake()->unique()->slug(),
            'address' => fake()->address(),
            'latitude' => 8.8471,
            'longitude' => 7.8736,
            'phone' => '+234 ' . fake()->numerify('##########'),
            'email' => fake()->companyEmail(),
            'is_active' => true,
        ];
    }
}
