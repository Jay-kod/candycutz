<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate 3: Feature flag enforcement.
 *
 * Usage in routes: ->middleware('feature:social_login')
 *
 * Feature flags are stored in the settings table with keys like
 * `feature:social_login`, `feature:online_payments`, `feature:booking`.
 * Value '1'/'true' = enabled (default); '0'/'false' = disabled.
 */
class FeatureGateMiddleware
{
    private const CACHE_TTL_SECONDS = 300; // 5 minutes

    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        foreach ($features as $feature) {
            if (! $this->isEnabled($feature)) {
                return ApiResponse::error(
                    'This feature is currently unavailable.',
                    [],
                    503,
                    'FEATURE_DISABLED',
                    'none',
                    context: [
                        'feature' => $feature,
                    ]
                );
            }
        }

        return $next($request);
    }

    private function isEnabled(string $feature): bool
    {
        $cacheKey = "feature_gate:{$feature}";

        $value = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($feature) {
            try {
                $raw = DB::table('settings')
                    ->where('key', "feature:{$feature}")
                    ->value('value');

                // If the feature flag doesn't exist in settings, default to enabled
                if ($raw === null) {
                    return true;
                }

                return in_array(strtolower(trim((string) $raw)), ['1', 'true', 'yes', 'on'], true);
            } catch (\Throwable) {
                // If DB is down, default to enabled (don't block users due to DB outage)
                return true;
            }
        });

        return (bool) $value;
    }
}
