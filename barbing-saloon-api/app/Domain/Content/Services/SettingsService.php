<?php

declare(strict_types=1);

namespace App\Domain\Content\Services;

use App\Models\Setting;

class SettingsService
{
    public function settings(): array
    {
        return Setting::all()
            ->groupBy('group')
            ->map(fn ($group) => $group->keyBy('key')->map(fn ($item) => $item->value))
            ->all();
    }
}