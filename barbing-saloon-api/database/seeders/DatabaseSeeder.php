<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
            BarberSeeder::class,
            WorkingHourSeeder::class,
            GallerySeeder::class,
            TestimonialSeeder::class,
            BlogSeeder::class,
            SettingSeeder::class,
            AppointmentSeeder::class,
        ]);
    }
}