<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ApiGateLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ApiGateLog> */
class ApiGateLogFactory extends Factory
{
    protected $model = ApiGateLog::class;

    public function definition(): array
    {
        return [
            'method' => fake()->randomElement(['GET', 'POST', 'PATCH', 'PUT', 'DELETE']),
            'path' => '/api/v1/'.fake()->randomElement(['services', 'appointments', 'barbers', 'gallery', 'blog']),
            'client_type' => fake()->randomElement(['web', 'mobile']),
            'status_code' => 200,
            'duration_ms' => fake()->numberBetween(15, 250),
            'query_count' => fake()->numberBetween(1, 12),
            'query_duration_ms' => fake()->numberBetween(2, 45),
            'memory_bytes' => fake()->numberBetween(2000000, 8000000),
            'user_id' => User::factory(),
            'user_role' => 'customer',
            'ip_address' => fake()->ipv4(),
            'is_slow' => false,
            'budget_exceeded' => false,
            'created_at' => now(),
        ];
    }
}
