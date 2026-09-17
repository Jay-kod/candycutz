<?php

namespace Database\Factories;

use App\Models\ThemeSetting;
use App\Models\ThemeVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ThemeVersion> */
class ThemeVersionFactory extends Factory
{
    protected $model = ThemeVersion::class;

    public function definition(): array
    {
        return [
            'theme_setting_id' => ThemeSetting::factory(),
            'version_number' => fake()->numberBetween(1, 20),
            'tokens_json' => ['brand_primary' => '#C6A15B'],
            'published_by_user_id' => User::factory()->admin(),
            'notes' => fake()->sentence(),
            'created_at' => now(),
        ];
    }
}
