<?php

namespace Database\Factories;

use App\Core\Enums\BlogStatus;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BlogPost> */
class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'title' => $title,
            'slug' => fake()->unique()->slug(),
            'excerpt' => fake()->sentence(),
            'body' => '<p>' . fake()->paragraph() . '</p>',
            'featured_image' => null,
            'author_id' => User::factory()->admin(),
            'status' => BlogStatus::draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state([
            'status' => BlogStatus::published,
            'published_at' => now(),
        ]);
    }
}
