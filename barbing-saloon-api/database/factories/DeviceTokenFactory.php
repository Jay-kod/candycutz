<?php

namespace Database\Factories;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DeviceToken> */
class DeviceTokenFactory extends Factory
{
    protected $model = DeviceToken::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->customer(),
            'token' => fake()->unique()->sha256(),
            'platform' => fake()->randomElement(['ios', 'android']),
            'last_seen_at' => now(),
        ];
    }
}
