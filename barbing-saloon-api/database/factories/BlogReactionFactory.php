<?php

namespace Database\Factories;

use App\Models\BlogPost;
use App\Models\BlogReaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BlogReaction> */
class BlogReactionFactory extends Factory
{
    protected $model = BlogReaction::class;

    public function definition(): array
    {
        return [
            'post_id' => BlogPost::factory(),
            'customer_id' => User::factory()->customer(),
            'reaction_type' => fake()->randomElement(['like', 'helpful']),
        ];
    }
}
