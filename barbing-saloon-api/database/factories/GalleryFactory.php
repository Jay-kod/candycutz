<?php

namespace Database\Factories;

use App\Core\Enums\GalleryCategory;
use App\Models\Barber;
use App\Models\Gallery;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Gallery> */
class GalleryFactory extends Factory
{
    protected $model = Gallery::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'image_path' => 'images/gallery/' . fake()->uuid() . '.jpg',
            'category' => fake()->randomElement(GalleryCategory::cases()),
            'barber_id' => Barber::factory(),
            'is_featured' => false,
            'display_order' => fake()->numberBetween(1, 20),
        ];
    }
}
