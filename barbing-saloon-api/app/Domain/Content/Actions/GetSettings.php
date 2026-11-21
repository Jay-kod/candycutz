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
            ->mapWithKeys(fn (Setting $setting) => [
                $setting->key => in_array($setting->key, ['brevo_api_key', 'mail_password'], true) && $setting->value
                    ? '••••••••'.substr((string) $setting->value, -4)
                    : $setting->value,
            ])
            ->all();
    }
}
