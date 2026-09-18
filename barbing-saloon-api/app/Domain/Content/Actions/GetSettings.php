<?php

namespace App\Domain\Content\Actions;

use App\Models\Setting;

class GetSettings
{
    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        return Setting::query()
            ->get()
            ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->value])
            ->all();
    }
}
