<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\User;
use Illuminate\Database\Seeder;

class BarberSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'bio' => 'Precision-focused barber with a strong eye for clean fades, sharp lines, and modern gentleman cuts.',
                'specialties' => ['fade', 'line-up', 'beard trim'],
                'years_experience' => 8,
                'instagram_url' => 'https://instagram.com/candycutzbarber1',
                'is_featured' => true,
            ],
            [
                'bio' => 'Friendly barber known for detailed kids cuts, classic styles, and comfortable first-time experiences.',
                'specialties' => ['kids cut', 'classic cut', 'scissor cut'],
                'years_experience' => 6,
                'instagram_url' => 'https://instagram.com/candycutzbarber2',
                'is_featured' => false,
            ],
            [
                'bio' => 'Premium grooming specialist handling combos, beards, and special event-ready finishing touches.',
                'specialties' => ['combo', 'beard shaping', 'special events'],
                'years_experience' => 10,
                'instagram_url' => 'https://instagram.com/candycutzbarber3',
                'is_featured' => false,
            ],
        ];

        User::where('role', 'barber')->orderBy('id')->get()->each(function (User $user, int $index) use ($profiles) {
            $profile = $profiles[$index] ?? $profiles[array_key_last($profiles)];

            Barber::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'bio' => $profile['bio'],
                    'specialties' => $profile['specialties'],
                    'years_experience' => $profile['years_experience'],
                    'instagram_url' => $profile['instagram_url'],
                    'display_order' => $index,
                    'is_featured' => $profile['is_featured'],
                ]
            );
        });
    }
}