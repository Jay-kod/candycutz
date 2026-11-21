<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\ThemeSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ThemeSetting> */
class ThemeSettingFactory extends Factory
{
    protected $model = ThemeSetting::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'theme_name' => 'CandyCutz Test Theme',
            'tokens_json' => ['brand_primary' => '#C6A15B'],
            'status' => 'draft',
        ];
    }
}
