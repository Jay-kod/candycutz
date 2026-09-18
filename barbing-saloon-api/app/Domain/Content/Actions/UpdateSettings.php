<?php

declare(strict_types=1);

namespace App\Domain\Content\Actions;

use App\Models\Setting;

class UpdateSettings
{
    /**
     * @param  array{settings: array<int, array{key: string, value: mixed, group: string}>}  $data
     */
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
