<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Shared\Services\CatalogueCache;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class SampleTestimonialsSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            'The fade was clean, precise, and exactly the style I asked for. I will definitely be back.',
            'A welcoming shop, professional service, and a sharp finish. Great experience from start to finish.',
            'The barber took time to understand what I wanted, and the result came out even better than expected.',
            'Excellent attention to detail on my haircut and beard trim. Everything felt comfortable and relaxed.',
            'Booking was easy, the service was on time, and I left feeling fresh. Highly recommend trying the shop.',
            'The team was friendly and professional, and the cut was finished with real care.',
            'Great atmosphere and a very neat haircut. I appreciated how carefully the barber checked the final look.',
            'I came in for a style refresh and got exactly what I hoped for. Clean work and a comfortable visit.',
            'The beard line-up was crisp and the whole appointment was smooth. I am happy with the result.',
            'Quality grooming and a welcoming experience. I would happily recommend CandyCutz to anyone looking for a fresh cut.',
        ];

        foreach ($reviews as $index => $review) {
            $number = $index + 1;

            Testimonial::firstOrCreate(
                ['client_name' => sprintf('Sample Customer %02d', $number)],
                [
                    'customer_id' => null,
                    'client_avatar' => 'settings/logo.png',
                    'rating' => 5,
                    'review' => $review,
                    'service_id' => null,
                    'barber_id' => null,
                    'is_approved' => true,
                    'is_featured' => false,
                ]
            );
        }

        CatalogueCache::bust('testimonials');
    }
}
