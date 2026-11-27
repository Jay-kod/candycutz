<?php

declare(strict_types=1);

namespace App\Domain\Content\Actions;

use App\Domain\Content\Services\SettingsService;
use App\Events\SettingsChanged;
use App\Models\Branch;
use App\Models\Setting;
use App\Support\InputSanitizer;
use Illuminate\Support\Facades\Cache;

class UpdateSettings
{
    /**
     * @param  array{settings: array<mixed>}  $data
     */
    public function execute(array $data): void
    {
        $settings = $data['settings'] ?? [];
        $previousCoords = Setting::whereIn('key', [
            'latitude',
            'longitude',
            'contact_map_iframe',
            'map_iframe',
            'contact_map_link',
            'map_link',
        ])->pluck('value', 'key');
        $savedFlat = [];

        foreach ($settings as $key => $setting) {
            if (is_array($setting) && isset($setting['key'])) {
                $settingKey = (string) $setting['key'];
                $newValue = $setting['value'] ?? null;

                if (in_array($settingKey, ['resend_api_key', 'brevo_api_key', 'mail_password'], true) && is_string($newValue) && str_starts_with($newValue, '••••••••')) {
                    continue;
                }

                $this->persistSetting($settingKey, $newValue, $setting['group'] ?? null);
                $savedFlat[$settingKey] = $newValue;
            } elseif (is_string($key)) {
                if (in_array($key, ['resend_api_key', 'brevo_api_key', 'mail_password'], true) && is_string($setting) && str_starts_with($setting, '••••••••')) {
                    continue;
                }

                $newValue = is_scalar($setting) ? (string) $setting : (is_null($setting) ? null : json_encode($setting));
                $this->persistSetting($key, $newValue);
                $savedFlat[$key] = $newValue;
            }
        }

        $this->syncShopLocationAndBranch($savedFlat, $previousCoords->all());

        app(SettingsService::class)->clearCache();
        Cache::forget('catalogue.shop_location');
    }

    private function persistSetting(string $key, mixed $newValue, ?string $group = null): void
    {
        $oldSetting = Setting::where('key', $key)->first();
        $oldValue = $oldSetting?->value;

        Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $newValue,
                'group' => $group ?? Setting::resolveGroupForKey($key),
            ]
        );

        SettingsChanged::dispatch($key, $oldValue, $newValue, auth()->user());
    }

    /**
     * Derive coordinates from Google Maps links when omitted or unchanged against an updated embed,
     * and keep active branch location metadata locked with CMS contact settings.
     *
     * @param  array<string, mixed>  $savedFlat
     * @param  array<string, mixed>  $previousCoords
     */
    private function syncShopLocationAndBranch(array $savedFlat, array $previousCoords = []): void
    {
        $hasExplicitCoords = isset($savedFlat['latitude'], $savedFlat['longitude'])
            && is_numeric($savedFlat['latitude'])
            && is_numeric($savedFlat['longitude']);

        $lat = $hasExplicitCoords ? round((float) $savedFlat['latitude'], 6) : null;
        $lng = $hasExplicitCoords ? round((float) $savedFlat['longitude'], 6) : null;

        $prevLat = isset($previousCoords['latitude']) && is_numeric($previousCoords['latitude'])
            ? round((float) $previousCoords['latitude'], 6)
            : null;
        $prevLng = isset($previousCoords['longitude']) && is_numeric($previousCoords['longitude'])
            ? round((float) $previousCoords['longitude'], 6)
            : null;
        $prevIframe = trim((string) ($previousCoords['contact_map_iframe'] ?? $previousCoords['map_iframe'] ?? ''));
        $prevLink = trim((string) ($previousCoords['contact_map_link'] ?? $previousCoords['map_link'] ?? ''));

        $submittedIframe = trim((string) ($savedFlat['contact_map_iframe'] ?? $savedFlat['map_iframe'] ?? $prevIframe));
        $submittedLink = trim((string) ($savedFlat['contact_map_link'] ?? $savedFlat['map_link'] ?? $prevLink));
        $coordsUnchangedFromStored = $hasExplicitCoords && $prevLat !== null && $prevLng !== null && $lat === $prevLat && $lng === $prevLng;
        $mapInputsChanged = ($submittedIframe !== '' && $submittedIframe !== $prevIframe)
            || ($submittedLink !== '' && $submittedLink !== $prevLink);

        $authoritativePin = InputSanitizer::resolveAuthoritativeMapPin($submittedIframe, $submittedLink);
        $iframeViewportLoc = InputSanitizer::extractGoogleMapsLocation($submittedIframe);
        $storedMatchesViewportOnly = $lat !== null && $lng !== null
            && $iframeViewportLoc !== null
            && ! $iframeViewportLoc['precise']
            && abs($lat - $iframeViewportLoc['lat']) < 0.00001
            && abs($lng - $iframeViewportLoc['lng']) < 0.00001;

        if (
            ! $hasExplicitCoords
            || ($coordsUnchangedFromStored && $mapInputsChanged)
            || ($storedMatchesViewportOnly && ($authoritativePin['precise'] ?? false))
        ) {
            if ($authoritativePin !== null) {
                $lat = $authoritativePin['lat'];
                $lng = $authoritativePin['lng'];
                $this->persistSetting('latitude', (string) $lat, 'contact');
                $this->persistSetting('longitude', (string) $lng, 'contact');
            }
        }

        $address = $savedFlat['contact_address'] ?? $savedFlat['address'] ?? null;
        $phone = $savedFlat['contact_phone'] ?? $savedFlat['phone'] ?? null;

        $branchUpdates = [];
        if ($lat !== null && $lng !== null && $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
            $branchUpdates['latitude'] = $lat;
            $branchUpdates['longitude'] = $lng;
        }
        if (is_string($address) && trim($address) !== '') {
            $trimmedAddress = trim($address);
            $branchUpdates['address'] = $trimmedAddress;
            if (array_key_exists('contact_address', $savedFlat)) {
                $this->persistSetting('address', $trimmedAddress, 'contact');
            }
        }
        if (is_string($phone) && trim($phone) !== '') {
            $branchUpdates['phone'] = trim($phone);
        }

        if ($branchUpdates !== []) {
            $branch = Branch::where('is_active', true)->first() ?? Branch::first();
            $branch?->update($branchUpdates);
        }
    }
}
