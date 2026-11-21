<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'hero_image_opacity'],
            [
                'value' => '22',
                'group' => 'hero',
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'hero_opacity'],
            [
                'value' => '22',
                'group' => 'hero',
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', ['hero_image_opacity', 'hero_opacity'])->delete();
    }
};
