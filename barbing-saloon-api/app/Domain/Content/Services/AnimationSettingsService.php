<?php

declare(strict_types=1);

namespace App\Domain\Content\Services;

use App\Domain\Shared\Services\CatalogueCache;
use App\Events\SettingsChanged;
use App\Models\Setting;
use App\Models\User;

class AnimationSettingsService
{
    public const ALLOWED_TYPES = [
        'none',
        'fade',
        'fade-up',
        'fade-down',
        'fade-left',
        'fade-right',
        'zoom-in',
        'zoom-out',
        'flip-x',
        'flip-y',
        'slide-up',
        'slide-left',
        'slide-right',
        'blur-in',
    ];

    public const ALLOWED_OVERRIDE_TYPES = [
        'inherit',
        'none',
        'fade',
        'fade-up',
        'fade-down',
        'fade-left',
        'fade-right',
        'zoom-in',
        'zoom-out',
        'flip-x',
        'flip-y',
        'slide-up',
        'slide-left',
        'slide-right',
        'blur-in',
    ];

    public const ALLOWED_EASINGS = [
        'linear',
        'ease',
        'ease-in',
        'ease-out',
        'ease-in-out',
        'cubic-bezier(0.16, 1, 0.3, 1)',
        'cubic-bezier(0.25, 0.1, 0.25, 1)',
        'cubic-bezier(0.4, 0, 0.2, 1)',
        'cubic-bezier(0, 0, 0.2, 1)',
    ];

    public const DEFAULT_SETTINGS = [
        'enabled' => true,
        'default_type' => 'fade-up',
        'duration_ms' => 700,
        'delay_ms' => 0,
        'easing' => 'cubic-bezier(0.16, 1, 0.3, 1)',
        'distance' => '30px',
        'stagger_ms' => 120,
        'trigger_threshold' => 0.15,
        'root_margin' => '0px 0px -40px 0px',
        'once' => true,
        'disable_on_mobile' => false,
        'respect_reduced_motion' => true,
        'overrides' => [
            'home' => [
                'hero' => ['type' => 'fade', 'duration_ms' => 500, 'delay_ms' => 0],
                'features' => ['type' => 'fade-up', 'duration_ms' => 600, 'stagger_ms' => 100],
                'stats' => ['type' => 'zoom-in', 'duration_ms' => 650, 'stagger_ms' => 150],
                'about' => ['type' => 'fade-up', 'duration_ms' => 700],
                'services' => ['type' => 'fade-up', 'duration_ms' => 700],
            ],
            'about' => [
                'shop' => ['type' => 'fade-up', 'duration_ms' => 700],
                'team' => ['type' => 'fade-up', 'duration_ms' => 700],
            ],
            'contact' => [
                'info' => ['type' => 'fade-right', 'duration_ms' => 600],
                'form' => ['type' => 'fade-left', 'duration_ms' => 600],
                'map' => ['type' => 'fade-up', 'duration_ms' => 700],
            ],
            'privacy' => [
                'hero' => ['type' => 'fade-down', 'duration_ms' => 600],
                'sections' => ['type' => 'fade-up', 'duration_ms' => 600, 'stagger_ms' => 50],
            ],
            'terms' => [
                'hero' => ['type' => 'fade-down', 'duration_ms' => 600],
                'sections' => ['type' => 'fade-up', 'duration_ms' => 600, 'stagger_ms' => 50],
            ],
            'account-deletion' => [
                'hero' => ['type' => 'fade-down', 'duration_ms' => 600],
                'guide' => ['type' => 'fade-up', 'duration_ms' => 650],
                'form' => ['type' => 'fade-up', 'duration_ms' => 650],
            ],
        ],
    ];

    /**
     * Get resolved animation settings with automatic caching.
     *
     * @return array<string, mixed>
     */
    public function getResolvedSettings(): array
    {
        return CatalogueCache::remember('animation_settings', 'resolved', function (): array {
            $settings = Setting::where('group', 'animation')->get()->keyBy('key');

            $enabledRaw = $settings->get('animation_enabled')?->value;
            $enabled = $enabledRaw === null ? self::DEFAULT_SETTINGS['enabled'] : filter_var($enabledRaw, FILTER_VALIDATE_BOOLEAN);

            $defaultType = $this->sanitizeType($settings->get('animation_default_type')?->value, self::DEFAULT_SETTINGS['default_type']);
            $durationMs = $this->clampInt($settings->get('animation_duration_ms')?->value, 50, 3000, self::DEFAULT_SETTINGS['duration_ms']);
            $delayMs = $this->clampInt($settings->get('animation_delay_ms')?->value, 0, 2000, self::DEFAULT_SETTINGS['delay_ms']);
            $easing = $this->sanitizeEasing($settings->get('animation_easing')?->value, self::DEFAULT_SETTINGS['easing']);
            $distance = $this->sanitizeDistance($settings->get('animation_distance')?->value, self::DEFAULT_SETTINGS['distance']);
            $staggerMs = $this->clampInt($settings->get('animation_stagger_ms')?->value, 0, 1000, self::DEFAULT_SETTINGS['stagger_ms']);
            $triggerThreshold = $this->clampFloat($settings->get('animation_trigger_threshold')?->value, 0.0, 1.0, self::DEFAULT_SETTINGS['trigger_threshold']);
            $rootMargin = $this->sanitizeRootMargin($settings->get('animation_root_margin')?->value, self::DEFAULT_SETTINGS['root_margin']);

            $onceRaw = $settings->get('animation_once')?->value;
            $once = $onceRaw === null ? self::DEFAULT_SETTINGS['once'] : filter_var($onceRaw, FILTER_VALIDATE_BOOLEAN);

            $disableMobileRaw = $settings->get('animation_disable_on_mobile')?->value;
            $disableOnMobile = $disableMobileRaw === null ? self::DEFAULT_SETTINGS['disable_on_mobile'] : filter_var($disableMobileRaw, FILTER_VALIDATE_BOOLEAN);

            $overridesRaw = $settings->get('animation_overrides')?->value;
            $overrides = self::DEFAULT_SETTINGS['overrides'];
            if ($overridesRaw) {
                $decoded = json_decode((string) $overridesRaw, true);
                if (is_array($decoded)) {
                    $overrides = $this->sanitizeOverrides($decoded);
                }
            }

            return [
                'enabled' => $enabled,
                'default_type' => $defaultType,
                'duration_ms' => $durationMs,
                'delay_ms' => $delayMs,
                'easing' => $easing,
                'distance' => $distance,
                'stagger_ms' => $staggerMs,
                'trigger_threshold' => $triggerThreshold,
                'root_margin' => $rootMargin,
                'once' => $once,
                'disable_on_mobile' => $disableOnMobile,
                'respect_reduced_motion' => true,
                'overrides' => $overrides,
            ];
        });
    }

    /**
     * Resolve effective animation properties for a specific page and named section.
     *
     * @return array<string, mixed>
     */
    public function resolveFor(string $page, ?string $section = null): array
    {
        $global = $this->getResolvedSettings();

        if (! $global['enabled']) {
            return [
                'enabled' => false,
                'type' => 'none',
                'duration_ms' => 0,
                'delay_ms' => 0,
                'easing' => $global['easing'],
                'distance' => '0px',
                'stagger_ms' => 0,
                'trigger_threshold' => $global['trigger_threshold'],
                'root_margin' => $global['root_margin'],
                'once' => $global['once'],
                'respect_reduced_motion' => true,
            ];
        }

        $type = $global['default_type'];
        $durationMs = $global['duration_ms'];
        $delayMs = $global['delay_ms'];
        $staggerMs = $global['stagger_ms'];

        $pageOverrides = $global['overrides'][$page] ?? null;
        if (is_array($pageOverrides)) {
            // Check direct section override first
            if ($section !== null && isset($pageOverrides[$section]) && is_array($pageOverrides[$section])) {
                $sectionOverride = $pageOverrides[$section];
                if (! empty($sectionOverride['type']) && $sectionOverride['type'] !== 'inherit') {
                    $type = $sectionOverride['type'];
                }
                if (isset($sectionOverride['duration_ms']) && is_numeric($sectionOverride['duration_ms'])) {
                    $durationMs = (int) $sectionOverride['duration_ms'];
                }
                if (isset($sectionOverride['delay_ms']) && is_numeric($sectionOverride['delay_ms'])) {
                    $delayMs = (int) $sectionOverride['delay_ms'];
                }
                if (isset($sectionOverride['stagger_ms']) && is_numeric($sectionOverride['stagger_ms'])) {
                    $staggerMs = (int) $sectionOverride['stagger_ms'];
                }
            } elseif (isset($pageOverrides['_default']) && is_array($pageOverrides['_default'])) {
                $pageDefault = $pageOverrides['_default'];
                if (! empty($pageDefault['type']) && $pageDefault['type'] !== 'inherit') {
                    $type = $pageDefault['type'];
                }
                if (isset($pageDefault['duration_ms']) && is_numeric($pageDefault['duration_ms'])) {
                    $durationMs = (int) $pageDefault['duration_ms'];
                }
                if (isset($pageDefault['delay_ms']) && is_numeric($pageDefault['delay_ms'])) {
                    $delayMs = (int) $pageDefault['delay_ms'];
                }
            }
        }

        $isEnabled = ($type !== 'none');

        return [
            'enabled' => $isEnabled,
            'type' => $type,
            'duration_ms' => $durationMs,
            'delay_ms' => $delayMs,
            'easing' => $global['easing'],
            'distance' => $global['distance'],
            'stagger_ms' => $staggerMs,
            'trigger_threshold' => $global['trigger_threshold'],
            'root_margin' => $global['root_margin'],
            'once' => $global['once'],
            'disable_on_mobile' => $global['disable_on_mobile'],
            'respect_reduced_motion' => true,
        ];
    }

    /**
     * Update animation settings with full validation and cache busting.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function update(array $validated, ?User $actor = null): array
    {
        $payload = [
            'animation_enabled' => isset($validated['enabled']) ? ($validated['enabled'] ? 'true' : 'false') : null,
            'animation_default_type' => isset($validated['default_type']) ? $this->sanitizeType($validated['default_type']) : null,
            'animation_duration_ms' => isset($validated['duration_ms']) ? (string) $this->clampInt($validated['duration_ms'], 50, 3000, 700) : null,
            'animation_delay_ms' => isset($validated['delay_ms']) ? (string) $this->clampInt($validated['delay_ms'], 0, 2000, 0) : null,
            'animation_easing' => isset($validated['easing']) ? $this->sanitizeEasing($validated['easing']) : null,
            'animation_distance' => isset($validated['distance']) ? $this->sanitizeDistance($validated['distance']) : null,
            'animation_stagger_ms' => isset($validated['stagger_ms']) ? (string) $this->clampInt($validated['stagger_ms'], 0, 1000, 120) : null,
            'animation_trigger_threshold' => isset($validated['trigger_threshold']) ? (string) $this->clampFloat($validated['trigger_threshold'], 0.0, 1.0, 0.15) : null,
            'animation_root_margin' => isset($validated['root_margin']) ? $this->sanitizeRootMargin($validated['root_margin']) : null,
            'animation_once' => isset($validated['once']) ? ($validated['once'] ? 'true' : 'false') : null,
            'animation_disable_on_mobile' => isset($validated['disable_on_mobile']) ? ($validated['disable_on_mobile'] ? 'true' : 'false') : null,
            'animation_overrides' => isset($validated['overrides']) && is_array($validated['overrides'])
                ? json_encode($this->sanitizeOverrides($validated['overrides']))
                : null,
        ];

        foreach ($payload as $key => $value) {
            if ($value !== null) {
                $oldSetting = Setting::where('key', $key)->first();
                $oldValue = $oldSetting?->value;

                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'group' => 'animation',
                    ]
                );

                SettingsChanged::dispatch($key, $oldValue, $value, $actor);
            }
        }

        // Bust all animation caches
        $this->bustCache();

        return $this->getResolvedSettings();
    }

    /**
     * Reset animation configuration to factory defaults.
     *
     * @return array<string, mixed>
     */
    public function resetToDefaults(?User $actor = null): array
    {
        $defaults = self::DEFAULT_SETTINGS;

        $payload = [
            'animation_enabled' => 'true',
            'animation_default_type' => $defaults['default_type'],
            'animation_duration_ms' => (string) $defaults['duration_ms'],
            'animation_delay_ms' => (string) $defaults['delay_ms'],
            'animation_easing' => $defaults['easing'],
            'animation_distance' => $defaults['distance'],
            'animation_stagger_ms' => (string) $defaults['stagger_ms'],
            'animation_trigger_threshold' => (string) $defaults['trigger_threshold'],
            'animation_root_margin' => $defaults['root_margin'],
            'animation_once' => 'true',
            'animation_disable_on_mobile' => 'false',
            'animation_overrides' => json_encode($defaults['overrides']),
        ];

        foreach ($payload as $key => $value) {
            $oldSetting = Setting::where('key', $key)->first();
            $oldValue = $oldSetting?->value;

            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'animation',
                ]
            );

            SettingsChanged::dispatch($key, $oldValue, $value, $actor);
        }

        $this->bustCache();

        return $this->getResolvedSettings();
    }

    /**
     * Bust all cache entries related to animation settings.
     */
    public function bustCache(): void
    {
        CatalogueCache::bust('animation_settings');
        CatalogueCache::bust('settings');
    }

    /**
     * Sanitization helper for animation type.
     */
    public function sanitizeType(mixed $type, string $default = 'fade-up'): string
    {
        if (is_string($type) && in_array($type, self::ALLOWED_TYPES, true)) {
            return $type;
        }

        return $default;
    }

    /**
     * Sanitization helper for override type (includes 'inherit').
     */
    public function sanitizeOverrideType(mixed $type, string $default = 'inherit'): string
    {
        if (is_string($type) && in_array($type, self::ALLOWED_OVERRIDE_TYPES, true)) {
            return $type;
        }

        return $default;
    }

    /**
     * Sanitization helper for easing curves.
     */
    public function sanitizeEasing(mixed $easing, string $default = 'cubic-bezier(0.16, 1, 0.3, 1)'): string
    {
        if (is_string($easing) && in_array($easing, self::ALLOWED_EASINGS, true)) {
            return $easing;
        }

        return $default;
    }

    /**
     * Sanitization helper for CSS distance. Ensures safe format like '30px', '2rem'.
     */
    public function sanitizeDistance(mixed $distance, string $default = '30px'): string
    {
        if (is_string($distance) && preg_match('/^[0-9]+(px|rem)$/', trim($distance))) {
            return trim($distance);
        }

        if (is_numeric($distance)) {
            return max(0, min(300, (int) $distance)).'px';
        }

        return $default;
    }

    /**
     * Sanitization helper for root margin.
     */
    public function sanitizeRootMargin(mixed $margin, string $default = '0px 0px -40px 0px'): string
    {
        if (is_string($margin)) {
            $clean = trim($margin);
            if (preg_match('/^[-0-9pxrem% ]+$/', $clean) && strlen($clean) <= 60) {
                return $clean;
            }
        }

        return $default;
    }

    /**
     * Helper to clamp an integer to sane boundaries.
     */
    public function clampInt(mixed $val, int $min, int $max, int $default): int
    {
        if (! is_numeric($val)) {
            return $default;
        }

        return max($min, min($max, (int) $val));
    }

    /**
     * Helper to clamp a float to sane boundaries.
     */
    public function clampFloat(mixed $val, float $min, float $max, float $default): float
    {
        if (! is_numeric($val)) {
            return $default;
        }

        return max($min, min($max, (float) $val));
    }

    /**
     * Sanitize overrides array.
     *
     * @param  array<string, mixed>  $rawOverrides
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function sanitizeOverrides(array $rawOverrides): array
    {
        $sanitized = [];

        foreach ($rawOverrides as $page => $sections) {
            $safePage = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $page));
            if (empty($safePage) || ! is_array($sections)) {
                continue;
            }

            $sanitized[$safePage] = [];

            foreach ($sections as $section => $options) {
                $safeSection = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $section));
                if (empty($safeSection) || ! is_array($options)) {
                    continue;
                }

                $entry = [];

                if (isset($options['type'])) {
                    $entry['type'] = $this->sanitizeOverrideType($options['type']);
                }

                if (isset($options['duration_ms']) && is_numeric($options['duration_ms'])) {
                    $entry['duration_ms'] = $this->clampInt($options['duration_ms'], 50, 3000, 700);
                }

                if (isset($options['delay_ms']) && is_numeric($options['delay_ms'])) {
                    $entry['delay_ms'] = $this->clampInt($options['delay_ms'], 0, 2000, 0);
                }

                if (isset($options['stagger_ms']) && is_numeric($options['stagger_ms'])) {
                    $entry['stagger_ms'] = $this->clampInt($options['stagger_ms'], 0, 1000, 120);
                }

                if (! empty($entry)) {
                    $sanitized[$safePage][$safeSection] = $entry;
                }
            }
        }

        return $sanitized;
    }
}
