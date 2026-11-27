<?php

namespace Database\Factories;

use App\Models\GalleryCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GalleryCategory> */
class GalleryCategoryFactory extends Factory
{
    protected $model = GalleryCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => fake()->unique()->slug(),
            'icon' => 'camera',
            'description' => fake()->sentence(),
            'display_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
