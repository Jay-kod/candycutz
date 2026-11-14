<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'hero_overlay_color'],
            [
                'value' => '#D4AF37',
                'group' => 'hero',
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'page_hero_home_overlay_color'],
            [
                'value' => '#D4AF37',
                'group' => 'hero',
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', ['hero_overlay_color', 'page_hero_home_overlay_color'])->delete();
    }
};

