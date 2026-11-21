<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $defaultPresets = json_encode([
            ['label' => 'Luxury Gold', 'value' => '#D4AF37'],
            ['label' => 'Warm Amber', 'value' => '#F59E0B'],
            ['label' => 'Deep Bronze', 'value' => '#B45309'],
            ['label' => 'Obsidian', 'value' => '#111827'],
            ['label' => 'Royal Emerald', 'value' => '#059669'],
            ['label' => 'Midnight Sapphire', 'value' => '#2563EB'],
            ['label' => 'Wine Velvet', 'value' => '#991B1B'],
        ]);

        Setting::firstOrCreate(
            ['key' => 'hero_overlay_presets'],
            [
                'value' => $defaultPresets,
                'group' => 'hero',
            ]
        );
    }

    public function down(): void
    {
        Setting::where('key', 'hero_overlay_presets')->delete();
    }
};

