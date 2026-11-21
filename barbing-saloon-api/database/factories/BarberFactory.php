<?php

namespace Database\Factories;

use App\Models\Barber;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Barber> */
class BarberFactory extends Factory
{
    protected $model = Barber::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->barber(),
            'branch_id' => Branch::factory(),
            'bio' => fake()->paragraph(),
            'specialties' => fake()->randomElements(['Classic Fade', 'Beard Grooming', 'Scissor Cut'], 2),
            'years_experience' => fake()->numberBetween(1, 15),
            'experience_years' => fake()->numberBetween(1, 15),
            'rating' => fake()->randomFloat(2, 3, 5),
            'is_available' => true,
            'is_home_service_ready' => true,
            'chair_status' => 'available',
            'instagram_url' => null,
            'display_order' => fake()->numberBetween(1, 20),
            'is_featured' => false,
        ];
    }
}
