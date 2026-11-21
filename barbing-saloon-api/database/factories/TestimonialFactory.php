<?php

namespace Database\Factories;

use App\Models\Barber;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Testimonial> */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'customer_id' => User::factory()->customer(),
            'client_name' => fake()->name(),
            'client_avatar' => null,
            'rating' => fake()->numberBetween(1, 5),
            'review' => fake()->paragraph(),
            'service_id' => Service::factory(),
            'barber_id' => Barber::factory(),
            'is_approved' => true,
            'is_featured' => false,
        ];
    }
}
