<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            KeffiOperationsSeeder::class,
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
            UserSeeder::class,
            WebUserSeeder::class,
            WorkingHourSeeder::class,
            SettingSeeder::class,
            AnimationSettingsSeeder::class,
            AppointmentSeeder::class,
            SampleTestimonialsSeeder::class,
            BlogSeeder::class,
            LoginDemoUserSeeder::class,
            DemoCatalogSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
