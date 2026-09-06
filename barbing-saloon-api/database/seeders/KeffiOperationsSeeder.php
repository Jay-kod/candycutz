<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Business;
use App\Models\ServiceZone;
use App\Models\ThemeSetting;
use App\Models\ThemeVersion;
use Illuminate\Database\Seeder;

class KeffiOperationsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Root Business
        $business = Business::firstOrCreate(
            ['name' => 'CandyCutz Enterprise'],
            [
                'legal_name' => 'CandyCutz Grooming Ltd.',
                'phone' => '+234 810 000 0000',
                'email' => 'concierge@candycutz.com',
                'logo' => '/images/logo.png',
            ]
        );

        // 2. Keffi Central Branch
        $branch = Branch::firstOrCreate(
            ['slug' => 'keffi-central'],
            [
                'business_id' => $business->id,
                'name' => 'Keffi Main Hub',
                'address' => 'Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments, BCG, Keffi 961101, Nasarawa, Nigeria',
                'latitude' => 8.84710000,
                'longitude' => 7.87360000,
                'phone' => '+234 810 000 0000',
                'email' => 'keffi@candycutz.com',
                'is_active' => true,
            ]
        );

        // 3. Service Zones
        ServiceZone::firstOrCreate(
            ['branch_id' => $branch->id, 'name' => 'Keffi Central Zone'],
            [
                'description' => 'BCG, Angwan Kare, Main Market, Total Filling Station corridor.',
                'radius_km' => 5.00,
                'base_travel_fee' => 2000.00,
                'per_km_fee' => 100.00,
                'is_active' => true,
            ]
        );

        ServiceZone::firstOrCreate(
            ['branch_id' => $branch->id, 'name' => 'University Axis Zone'],
            [
                'description' => 'Nasarawa State University (NSUK) Main Campus, High Court, Pyanku, GRA.',
                'radius_km' => 12.00,
                'base_travel_fee' => 3000.00,
                'per_km_fee' => 150.00,
                'is_active' => true,
            ]
        );

        ServiceZone::firstOrCreate(
            ['branch_id' => $branch->id, 'name' => 'Outskirts Zone'],
            [
                'description' => 'Akwanga Road Axis, Gidan Zakara, Keffi Bypass corridor.',
                'radius_km' => 20.00,
                'base_travel_fee' => 4500.00,
                'per_km_fee' => 200.00,
                'is_active' => true,
            ]
        );

        // 4. Baseline Theme Studio Configuration
        $themeTokens = [
            'theme_name' => 'CandyCutz Champagne Royal',
            'light' => [
                'background' => '#F7F6F2',
                'surface' => '#FFFFFF',
                'surface_elevated' => '#F0EDE6',
                'text_primary' => '#111111',
                'text_secondary' => '#666666',
                'border' => '#E5E2DB',
                'brand_primary' => '#C6A15B',
                'brand_dark' => '#9B7735',
            ],
            'dark' => [
                'background' => '#0B0B0B',
                'surface' => '#151515',
                'surface_elevated' => '#1D1D1D',
                'text_primary' => '#F5F3EE',
                'text_secondary' => '#A9A7A1',
                'border' => '#2A2A2A',
                'brand_primary' => '#D2AE68',
                'brand_dark' => '#A9823F',
            ],
            'typography' => [
                'font_family_sans' => 'Inter',
                'font_family_display' => 'Playfair Display',
                'heading_weight' => '700',
                'body_weight' => '400',
            ],
        ];

        $themeSetting = ThemeSetting::firstOrCreate(
            ['branch_id' => $branch->id],
            [
                'theme_name' => 'CandyCutz Champagne Royal',
                'tokens_json' => $themeTokens,
                'status' => 'published',
            ]
        );

        ThemeVersion::firstOrCreate(
            ['theme_setting_id' => $themeSetting->id, 'version_number' => 1],
            [
                'tokens_json' => $themeTokens,
                'notes' => 'Initial luxury theme release.',
            ]
        );
    }
}
