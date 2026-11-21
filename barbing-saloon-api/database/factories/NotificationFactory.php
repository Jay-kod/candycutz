<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Notification> */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'sender_id' => User::factory()->admin(),
            'recipient_type' => 'customer',
            'recipient_id' => User::factory()->customer(),
            'type' => 'appointment',
            'title' => fake()->sentence(4),
            'message' => fake()->sentence(),
            'related_entity_id' => null,
            'is_read' => false,
            'created_at' => now(),
        ];
    }
}
