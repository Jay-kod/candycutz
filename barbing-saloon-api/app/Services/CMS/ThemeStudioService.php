<?php

declare(strict_types=1);

namespace App\Services\CMS;

use App\Models\ThemeSetting;
use App\Models\ThemeVersion;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ThemeStudioService
{
    protected string $cacheKey = 'candycutz_published_theme_tokens';

    /**
     * Get published theme tokens (cached for 24 hours or until version publish).
     */
    public function getPublishedTheme(): array
    {
        return Cache::remember($this->cacheKey, 86400, function () {
            $setting = ThemeSetting::where('status', 'published')->latest()->first();
            if ($setting && !empty($setting->tokens_json)) {
                return $setting->tokens_json;
            }

            // Fallback luxury tokens
            return [
                'theme_name' => 'CandyCutz Champagne Royal',
                'light' => [
                    'background' => '#F7F6F2',
                    'surface' => '#FFFFFF',
                    'surface_elevated' => '#F0EDE6',
                    'text_primary' => '#111111',
                    'text_secondary' => '#666666',
                    'border' => '#E5E2DB',
                    'brand_primary' => '#C6A15B',
                    'brand_dark' => '#9B7735',
                ],
                'dark' => [
                    'background' => '#0B0B0B',
                    'surface' => '#151515',
                    'surface_elevated' => '#1D1D1D',
                    'text_primary' => '#F5F3EE',
                    'text_secondary' => '#A9A7A1',
                    'border' => '#2A2A2A',
                    'brand_primary' => '#D2AE68',
                    'brand_dark' => '#A9823F',
                ],
                'typography' => [
                    'font_family_sans' => 'Inter',
                    'font_family_display' => 'Playfair Display',
                    'heading_weight' => '700',
                    'body_weight' => '400',
                ],
            ];
        });
    }

    /**
     * Publish a new theme version.
     */
    public function publishThemeVersion(ThemeSetting $setting, array $tokens, User $publisher, ?string $notes = null): ThemeVersion
    {
        return DB::transaction(function () use ($setting, $tokens, $publisher, $notes) {
            $lastVersion = ThemeVersion::where('theme_setting_id', $setting->id)->max('version_number') ?? 0;
            $nextVersionNumber = $lastVersion + 1;

            $version = ThemeVersion::create([
                'theme_setting_id' => $setting->id,
                'version_number' => $nextVersionNumber,
                'tokens_json' => $tokens,
                'published_by_user_id' => $publisher->id,
                'notes' => $notes ?: "Published version {$nextVersionNumber}",
                'created_at' => now(),
            ]);

            $setting->update([
                'tokens_json' => $tokens,
                'status' => 'published',
            ]);

            Cache::forget($this->cacheKey);

            return $version;
        });
    }

    /**
     * Roll back to a previous theme version.
     */
    public function rollbackVersion(ThemeSetting $setting, int $versionNumber): ThemeSetting
    {
        return DB::transaction(function () use ($setting, $versionNumber) {
            $targetVersion = ThemeVersion::where('theme_setting_id', $setting->id)
                ->where('version_number', $versionNumber)
                ->firstOrFail();

            $setting->update([
                'tokens_json' => $targetVersion->tokens_json,
                'status' => 'published',
            ]);

            Cache::forget($this->cacheKey);

            return $setting;
        });
    }
}
