<?php

declare(strict_types=1);

namespace App\Domain\Content\Actions;

use App\Models\Setting;

class UpdateSettings
{
    /**
     * @param  array{settings: array<mixed>}  $data
     */
    public function execute(array $data): void
    {
        $settings = $data['settings'] ?? [];

        foreach ($settings as $key => $setting) {
            if (is_array($setting) && isset($setting['key'])) {
                $settingKey = (string) $setting['key'];
                Setting::updateOrCreate(
                    ['key' => $settingKey],
                    [
                        'value' => $setting['value'] ?? null,
                        'group' => $setting['group'] ?? Setting::resolveGroupForKey($settingKey),
                    ]
                );
            } elseif (is_string($key)) {
                if (in_array($key, ['brevo_api_key', 'mail_password'], true) && is_string($setting) && str_starts_with($setting, '••••••••')) {
                    continue;
                }

                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => is_scalar($setting) ? (string) $setting : (is_null($setting) ? null : json_encode($setting)),
                        'group' => Setting::resolveGroupForKey($key),
                    ]
                );
            }
        }
    }
}
