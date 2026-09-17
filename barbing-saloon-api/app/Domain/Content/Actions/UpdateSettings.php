<?php

declare(strict_types=1);

namespace App\Domain\Content\Actions;

use App\Models\Setting;

class UpdateSettings
{
    public function execute(array $data): void
    {
        foreach ($data['settings'] as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group']]
            );
        }
    }
}