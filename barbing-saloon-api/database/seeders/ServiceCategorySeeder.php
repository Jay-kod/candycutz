<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Haircut', 'Beard', 'Combo', 'Kids', 'Special'] as $index => $name) {
            ServiceCategory::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'icon' => null, 'display_order' => $index]);
        }
    }
}