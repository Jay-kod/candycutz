<?php

declare(strict_types=1);

namespace App\Domain\Content\Services;

use App\Domain\Shared\Services\CatalogueCache;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Groups safe for unauthenticated public consumption.
     * 'notifications' is excluded because it contains brevo_api_key, mail_password, etc.
     */
    private const PUBLIC_GROUPS = [
        'general',
        'hero',
        'about',
        'features',
        'contact',
        'payment',
        'booking',
        'social',
        'seo',
        'theme',
        'mobile',
        'legal',
        'content',
        'animation',
    ];

    /**
     * Return ALL settings (admin-only).
     *
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return Cache::remember('settings.all', 3600, function () {
            $settings = Setting::all();

            $grouped = $settings
                ->groupBy('group')
                ->map(fn ($group) => $group->keyBy('key')->map(fn ($item) => $item->value))
                ->all();

            $flat = $settings
                ->keyBy('key')
                ->map(fn ($item) => $item->value)
                ->all();

            $merged = array_merge($flat, $grouped);

            $googleEnabled = filter_var(
                $merged['google_auth_enabled'] ?? true,
                FILTER_VALIDATE_BOOLEAN
            );
            $merged['google_auth_enabled'] = $googleEnabled ? 'true' : 'false';
            if (! $googleEnabled) {
                unset($merged['google_client_id'], $merged['google_client_ids']);
                if (isset($merged['social']) && is_array($merged['social'])) {
                    unset(
                        $merged['social']['google_client_id'],
                        $merged['social']['google_client_ids']
                    );
                }
            }

            /** @var AnimationSettingsService $animationService */
            $animationService = app(AnimationSettingsService::class);
            $merged['animation_resolved'] = $animationService->getResolvedSettings();

            return $merged;
        });
    }

    /**
     * Return settings for a specific group with dedicated cache key.
     *
     * @return array<string, mixed>
     */
    public function groupSettings(string $group): array
    {
        return Cache::remember("settings.group.{$group}", 3600, function () use ($group) {
            $settings = Setting::where('group', $group)->get();

            return $settings->keyBy('key')->map(fn ($item) => $item->value)->all();
        });
    }

    /**
     * Return only public-safe settings (no secrets).
     *
     * @return array<string, mixed>
     */
    public function publicSettings(): array
    {
        return Cache::remember('settings.public', 3600, function () {
            $grouped = [];
            $flat = [];

            foreach (self::PUBLIC_GROUPS as $group) {
                $groupData = $this->groupSettings($group);
                if (! empty($groupData)) {
                    $grouped[$group] = $groupData;
                    $flat = array_merge($flat, $groupData);
                }
            }

            $merged = array_merge($flat, $grouped);

            /** @var AnimationSettingsService $animationService */
            $animationService = app(AnimationSettingsService::class);
            $merged['animation_resolved'] = $animationService->getResolvedSettings();

            return $merged;
        });
    }

    /**
     * Clear all cached settings across all layers and groups.
     */
    public function clearCache(): void
    {
        Cache::forget('settings.all');
        Cache::forget('settings.public');
        Cache::forget('system_settings');
        Cache::forget('policies.no_show');
        Cache::forget('policies.refund');
        Cache::forget('catalogue.shop_location');

        foreach (self::PUBLIC_GROUPS as $group) {
            Cache::forget("settings.group.{$group}");
        }

        CatalogueCache::bust('settings');
    }
}
