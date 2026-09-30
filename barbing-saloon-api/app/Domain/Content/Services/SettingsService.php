<?php

declare(strict_types=1);

namespace App\Domain\Content\Services;

use App\Models\Setting;

class SettingsService
{
    /**
     * Groups safe for unauthenticated public consumption.
     * 'notifications' is excluded because it contains brevo_api_key, mail_password, etc.
     */
    private const PUBLIC_GROUPS = [
        'general',
        'hero',
        'contact',
        'booking',
        'social',
        'seo',
        'theme',
        'mobile',
        'legal',
    ];

    /**
     * Return ALL settings (admin-only).
     *
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return Setting::all()
            ->groupBy('group')
            ->map(fn ($group) => $group->keyBy('key')->map(fn ($item) => $item->value))
            ->all();
    }

    /**
     * Return only public-safe settings (no secrets).
     *
     * @return array<string, mixed>
     */
    public function publicSettings(): array
    {
        return Setting::whereIn('group', self::PUBLIC_GROUPS)
            ->get()
            ->groupBy('group')
            ->map(fn ($group) => $group->keyBy('key')->map(fn ($item) => $item->value))
            ->all();
    }
}

