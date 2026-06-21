<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ServiceCategory::query()->pluck('id', 'slug');

        if ($categories->isEmpty()) {
            return;
        }

        $services = [
            ['slug' => 'classic-cut', 'name' => 'Classic Cut', 'description' => 'Clean all-purpose haircut with a sharp finish.', 'price' => 2500, 'duration_minutes' => 30, 'category' => 'haircut'],
            ['slug' => 'premium-fade', 'name' => 'Premium Fade', 'description' => 'Detailed fade with smooth blending and crisp edges.', 'price' => 4000, 'duration_minutes' => 45, 'category' => 'haircut'],
            ['slug' => 'beard-trim', 'name' => 'Beard Trim', 'description' => 'Neat beard shaping and line-up.', 'price' => 2000, 'duration_minutes' => 30, 'category' => 'beard'],
            ['slug' => 'beard-treatment', 'name' => 'Beard Treatment', 'description' => 'Beard conditioning and styling service.', 'price' => 3000, 'duration_minutes' => 45, 'category' => 'beard'],
            ['slug' => 'combo-cut', 'name' => 'Combo Cut', 'description' => 'Haircut and beard service in one appointment.', 'price' => 5000, 'duration_minutes' => 60, 'category' => 'combo'],
            ['slug' => 'gold-combo', 'name' => 'Gold Combo', 'description' => 'Premium haircut, beard, and finish package.', 'price' => 6500, 'duration_minutes' => 75, 'category' => 'combo'],
            ['slug' => 'kids-cut', 'name' => 'Kids Cut', 'description' => 'Friendly haircut service for children.', 'price' => 1500, 'duration_minutes' => 30, 'category' => 'kids'],
            ['slug' => 'kids-fade', 'name' => 'Kids Fade', 'description' => 'Stylish fade designed for younger clients.', 'price' => 2500, 'duration_minutes' => 45, 'category' => 'kids'],
            ['slug' => 'special-executive-cut', 'name' => 'Executive Cut', 'description' => 'Luxury grooming package for formal occasions.', 'price' => 7000, 'duration_minutes' => 60, 'category' => 'special'],
            ['slug' => 'special-wedding-package', 'name' => 'Wedding Package', 'description' => 'Full premium grooming session for special events.', 'price' => 8000, 'duration_minutes' => 90, 'category' => 'special'],
        ];

        foreach ($services as $index => $service) {
            $categoryId = $categories->get($service['category']);

            if (! $categoryId) {
                continue;
            }

            Service::updateOrCreate(
                ['slug' => $service['slug']],
                [
                    'name' => $service['name'],
                    'description' => $service['description'],
                    'price' => $service['price'],
                    'duration_minutes' => $service['duration_minutes'],
                    'category_id' => $categoryId,
                    'image' => null,
                    'is_active' => true,
                    'display_order' => $index,
                ]
            );
        }
    }
}