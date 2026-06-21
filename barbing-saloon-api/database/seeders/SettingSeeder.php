<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['group' => 'general', 'key' => 'saloon_name', 'value' => 'CandyCutz'],
            ['group' => 'general', 'key' => 'tagline', 'value' => 'Premium cuts. Clean finish.'],
            ['group' => 'general', 'key' => 'logo', 'value' => 'settings/logo.png'],
            ['group' => 'general', 'key' => 'favicon', 'value' => 'settings/favicon.png'],
            ['group' => 'contact', 'key' => 'phone', 'value' => '08030000000'],
            ['group' => 'contact', 'key' => 'email', 'value' => 'hello@candycutz.com'],
            ['group' => 'contact', 'key' => 'address', 'value' => '12 Allen Avenue, Ikeja, Lagos'],
            ['group' => 'contact', 'key' => 'map_link', 'value' => 'https://maps.google.com/?q=CandyCutz'],
            ['group' => 'social', 'key' => 'instagram', 'value' => 'https://instagram.com/candycutz'],
            ['group' => 'social', 'key' => 'facebook', 'value' => 'https://facebook.com/candycutz'],
            ['group' => 'social', 'key' => 'whatsapp', 'value' => '2348030000000'],
            ['group' => 'seo', 'key' => 'meta_title', 'value' => 'CandyCutz | Premium Barbing Saloon'],
            ['group' => 'seo', 'key' => 'meta_description', 'value' => 'Book premium haircuts, beard grooming, and combo services at CandyCutz.'],
            ['group' => 'booking', 'key' => 'deposit_amount', 'value' => '500'],
            ['group' => 'booking', 'key' => 'min_notice_hours', 'value' => '2'],
            ['group' => 'booking', 'key' => 'max_days_ahead', 'value' => '30'],
            ['group' => 'booking', 'key' => 'slot_interval_minutes', 'value' => '30'],
            ['group' => 'theme', 'key' => 'primary_color', 'value' => '#C9A84C'],
            ['group' => 'theme', 'key' => 'accent_color', 'value' => '#E8C96A'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'group' => $setting['group'],
                    'value' => $setting['value'],
                ]
            );
        }
    }
}