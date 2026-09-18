<?php

namespace Database\Factories;

use App\Domain\Shared\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'real_name' => fake()->name(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => UserRole::customer,
            'phone' => fake()->numerify('080########'),
            'status' => 'active',
            'is_active' => true,
            'notification_preferences' => [],
        ];
    }

    public function customer(): static
    {
        return $this->state(['role' => UserRole::customer]);
    }

    public function barber(): static
    {
        return $this->state(['role' => UserRole::barber]);
    }

    public function admin(): static
    {
        return $this->state(['role' => UserRole::admin]);
    }

    public function superAdmin(): static
    {
        return $this->state(['role' => UserRole::super_admin]);
    }
}
