<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Content\Services\AnimationSettingsService;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class AnimationSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = AnimationSettingsService::DEFAULT_SETTINGS;

        $items = [
            'animation_enabled' => $defaults['enabled'] ? 'true' : 'false',
            'animation_default_type' => $defaults['default_type'],
            'animation_duration_ms' => (string) $defaults['duration_ms'],
            'animation_delay_ms' => (string) $defaults['delay_ms'],
            'animation_easing' => $defaults['easing'],
            'animation_distance' => $defaults['distance'],
            'animation_stagger_ms' => (string) $defaults['stagger_ms'],
            'animation_trigger_threshold' => (string) $defaults['trigger_threshold'],
            'animation_root_margin' => $defaults['root_margin'],
            'animation_once' => $defaults['once'] ? 'true' : 'false',
            'animation_disable_on_mobile' => $defaults['disable_on_mobile'] ? 'true' : 'false',
            'animation_overrides' => json_encode($defaults['overrides']),
        ];

        foreach ($items as $key => $value) {
            Setting::firstOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'animation',
                ]
            );
        }
    }
}
