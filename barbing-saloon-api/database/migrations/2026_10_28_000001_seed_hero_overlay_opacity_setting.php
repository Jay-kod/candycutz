<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'hero_overlay_opacity'],
            [
                'value' => '45',
                'group' => 'hero',
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'page_hero_home_overlay_opacity'],
            [
                'value' => '45',
                'group' => 'hero',
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', ['hero_overlay_opacity'])->delete();
    }
};

