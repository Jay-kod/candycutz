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
 * Gate 2: Validate X-Client header and enforce minimum app version per platform.
 *
 * Allows operators to kill-switch broken client builds by raising
 * the minimum version in the settings table.
 */
class ClientGateMiddleware
{
    private const KNOWN_CLIENTS = ['web', 'mobile-ios', 'mobile-android', 'admin'];

    private const CACHE_TTL_SECONDS = 300; // 5 minutes

    public function handle(Request $request, Closure $next): Response
    {
        $client = $request->header('X-Client');
        $appVersion = $request->header('X-App-Version');

        // If no X-Client header, let the request through (backwards compat).
        // RequestContext already defaults unidentified clients.
        if (! $client) {
            return $next($request);
        }

        // Normalize
        $client = strtolower(trim($client));

        // Validate known client
        if (! in_array($client, self::KNOWN_CLIENTS, true)) {
            // Unknown client — let through but don't enforce version.
            return $next($request);
        }

        // Version enforcement only applies to mobile clients
        if (! str_starts_with($client, 'mobile-')) {
            return $next($request);
        }

        if (! $appVersion || ! is_string($appVersion)) {
            return $next($request);
        }

        $appVersion = trim($appVersion);

        // Get minimum version from settings (cached)
        $minVersion = $this->getMinVersion($client);

        if ($minVersion && version_compare($appVersion, $minVersion, '<')) {
            return ApiResponse::error(
                'Please update the app to the latest version to continue.',
                [],
                426,
                'APP_UPDATE_REQUIRED',
                'update_app',
                context: [
                    'min_version' => $minVersion,
                    'current_version' => $appVersion,
                    'platform' => $client,
                ]
            );
        }

        return $next($request);
    }

    private function getMinVersion(string $client): ?string
    {
        $settingKey = match ($client) {
            'mobile-ios' => 'min_ios_app_version',
            'mobile-android' => 'min_android_app_version',
            default => null,
        };

        if (! $settingKey) {
            return null;
        }

        return Cache::remember(
            "client_gate:{$settingKey}",
            self::CACHE_TTL_SECONDS,
            function () use ($settingKey) {
                try {
                    return DB::table('settings')
                        ->where('key', $settingKey)
                        ->value('value');
                } catch (\Throwable) {
                    return null;
                }
            }
        );
    }
}
