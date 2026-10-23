<?php

declare(strict_types=1);

namespace App\Domain\Content\Actions;

use App\Events\SettingsChanged;
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
                $oldSetting = Setting::where('key', $settingKey)->first();
                $oldValue = $oldSetting?->value;
                $newValue = $setting['value'] ?? null;

                Setting::updateOrCreate(
                    ['key' => $settingKey],
                    [
                        'value' => $newValue,
                        'group' => $setting['group'] ?? Setting::resolveGroupForKey($settingKey),
                    ]
                );

                SettingsChanged::dispatch($settingKey, $oldValue, $newValue, auth()->user());
            } elseif (is_string($key)) {
                if (in_array($key, ['brevo_api_key', 'mail_password'], true) && is_string($setting) && str_starts_with($setting, '••••••••')) {
                    continue;
                }

                $oldSetting = Setting::where('key', $key)->first();
                $oldValue = $oldSetting?->value;
                $newValue = is_scalar($setting) ? (string) $setting : (is_null($setting) ? null : json_encode($setting));

                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $newValue,
                        'group' => Setting::resolveGroupForKey($key),
                    ]
                );

                SettingsChanged::dispatch($key, $oldValue, $newValue, auth()->user());
            }
        }
    }
}
