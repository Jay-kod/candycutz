<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Service> */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'branch_id' => Branch::factory(),
            'category_id' => ServiceCategory::factory(),
            'name' => $name,
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 1000, 15000),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
            'image' => null,
            'home_service_allowed' => true,
            'is_active' => true,
            'is_featured' => false,
            'display_order' => fake()->numberBetween(1, 20),
        ];
    }
}
