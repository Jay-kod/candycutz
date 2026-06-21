<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->orderBy('id')->get();
        $services = Service::orderBy('id')->get();
        $barbers = Barber::orderBy('id')->get();

        $reviews = [
            'The fade was sharp and the whole experience felt premium from start to finish.',
            'Excellent attention to detail. I left looking clean and confident.',
            'My son loved the haircut and the barber was very patient with him.',
            'The beard line-up came out perfect and the service was quick.',
            'Best grooming experience I have had in a long time.',
            'Comfortable space, great music, and very professional staff.',
            'I booked a combo and the result was exactly what I wanted.',
            'The barber understood the style I wanted immediately.',
            'Friendly team, smooth process, and a great finish.',
            'A premium barbing saloon with quality service every time.',
        ];

        foreach ($reviews as $index => $review) {
            $customer = $customers[$index % max($customers->count(), 1)] ?? null;
            $service = $services[$index % max($services->count(), 1)] ?? null;
            $barber = $barbers[$index % max($barbers->count(), 1)] ?? null;

            Testimonial::updateOrCreate(
                ['client_name' => $customer?->name ?? 'Client ' . ($index + 1)],
                [
                    'customer_id' => $customer?->id,
                    'client_avatar' => null,
                    'rating' => 3 + ($index % 3),
                    'review' => $review,
                    'service_id' => $service?->id,
                    'barber_id' => $barber?->id,
                    'is_approved' => $index % 2 === 0,
                    'is_featured' => $index < 3,
                ]
            );
        }
    }
}