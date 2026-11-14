<?php

declare(strict_types=1);

namespace App\Domain\Content\Services;

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

            /** @var AnimationSettingsService $animationService */
            $animationService = app(AnimationSettingsService::class);
            $merged['animation_resolved'] = $animationService->getResolvedSettings();

            return $merged;
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
            $settings = Setting::whereIn('group', self::PUBLIC_GROUPS)->get();

            $grouped = $settings
                ->groupBy('group')
                ->map(fn ($group) => $group->keyBy('key')->map(fn ($item) => $item->value))
                ->all();

            $flat = $settings
                ->keyBy('key')
                ->map(fn ($item) => $item->value)
                ->all();

            $merged = array_merge($flat, $grouped);

            /** @var AnimationSettingsService $animationService */
            $animationService = app(AnimationSettingsService::class);
            $merged['animation_resolved'] = $animationService->getResolvedSettings();

            return $merged;
        });
    }
}
