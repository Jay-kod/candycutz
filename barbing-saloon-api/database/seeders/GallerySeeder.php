<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $barberIds = Barber::query()->pluck('id')->all();
        $items = [
            ['title' => 'Fresh Fade Finish', 'description' => 'Sharp fade with clean edges.', 'image_path' => 'gallery/haircut/fade-1.jpg', 'category' => 'haircut'],
            ['title' => 'Studio Beard Line', 'description' => 'Detailed beard shaping and polish.', 'image_path' => 'gallery/beard/beard-1.jpg', 'category' => 'beard'],
            ['title' => 'Combo Session', 'description' => 'Haircut and beard combo for a full refresh.', 'image_path' => 'gallery/combo/combo-1.jpg', 'category' => 'combo'],
            ['title' => 'Before and After Cut', 'description' => 'Transformation image from trim to finish.', 'image_path' => 'gallery/before_after/before-after-1.jpg', 'category' => 'before_after'],
            ['title' => 'CandyCutz Interior', 'description' => 'Warm saloon seating and premium lighting.', 'image_path' => 'gallery/shop/shop-1.jpg', 'category' => 'shop'],
            ['title' => 'Kids Fade Style', 'description' => 'Neat and friendly kids haircut.', 'image_path' => 'gallery/haircut/kids-fade-1.jpg', 'category' => 'haircut'],
            ['title' => 'Line-Up Detail', 'description' => 'Crisp hairline and side profile finish.', 'image_path' => 'gallery/beard/beard-2.jpg', 'category' => 'beard'],
            ['title' => 'Wedding Combo Look', 'description' => 'Special-event grooming package.', 'image_path' => 'gallery/combo/combo-2.jpg', 'category' => 'combo'],
            ['title' => 'Classic Cut Photo', 'description' => 'Simple clean classic cut.', 'image_path' => 'gallery/haircut/classic-1.jpg', 'category' => 'haircut'],
            ['title' => 'Morning Setup', 'description' => 'Ready saloon before opening time.', 'image_path' => 'gallery/shop/shop-2.jpg', 'category' => 'shop'],
            ['title' => 'Fade Detail Closeup', 'description' => 'Precision fade work close up.', 'image_path' => 'gallery/haircut/fade-2.jpg', 'category' => 'haircut'],
            ['title' => 'Beard Sculpt', 'description' => 'Sculpted beard with defined shape.', 'image_path' => 'gallery/beard/beard-3.jpg', 'category' => 'beard'],
            ['title' => 'Combo Package Finish', 'description' => 'Finished combo package from chair to mirror.', 'image_path' => 'gallery/combo/combo-3.jpg', 'category' => 'combo'],
            ['title' => 'Before and After Beard', 'description' => 'Beard transformation comparison.', 'image_path' => 'gallery/before_after/before-after-2.jpg', 'category' => 'before_after'],
            ['title' => 'Premium Chair Area', 'description' => 'CandyCutz client-ready chair space.', 'image_path' => 'gallery/shop/shop-3.jpg', 'category' => 'shop'],
        ];

        foreach ($items as $index => $item) {
            $barberId = $barberIds[$index % max(count($barberIds), 1)] ?? null;

            Gallery::updateOrCreate(
                ['title' => $item['title']],
                [
                    'description' => $item['description'],
                    'image_path' => $item['image_path'],
                    'category' => $item['category'],
                    'barber_id' => $barberId,
                    'is_featured' => $index < 3,
                    'display_order' => $index,
                ]
            );
        }
    }
}