<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AuditLog> */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->admin(),
            'user_role' => 'admin',
            'action' => 'updated',
            'module' => 'testing',
            'target_type' => 'App\\Models\\Service',
            'target_id' => fake()->numberBetween(1, 100),
            'old_value' => [],
            'new_value' => [],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
