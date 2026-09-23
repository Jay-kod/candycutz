<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AppVersion;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAppVersion
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appVersion = $request->header('X-App-Version');
        $isMobileClient = $request->header('X-Client-Type') === 'mobile' || ! empty($appVersion);

        // Check if soft app maintenance mode is enabled
        if ($isMobileClient && ! $request->is('api/v1/admin/*') && ! $request->is('api/v1/superadmin/*') && ! $request->is('api/v1/super-admin/*')) {
            $isMaintenance = Setting::where('key', 'app_maintenance_mode')->value('value');
            if ($isMaintenance === 'true' || $isMaintenance === '1') {
                $message = Setting::where('key', 'app_maintenance_message')->value('value')
                    ?: 'The mobile app is undergoing scheduled maintenance. Please check back shortly.';

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'code' => 'MAINTENANCE_MODE',
                    'maintenance' => true,
                ], 503);
            }
        }

        // If app version is provided, verify against minimum supported version
        if (! empty($appVersion)) {
            $platform = strtolower($request->header('X-App-Platform', 'android'));
            if (! in_array($platform, ['ios', 'android'], true)) {
                $platform = 'android';
            }

            // Find minimum version rule for platform or general
            $minRule = AppVersion::query()
                ->where('is_minimum', true)
                ->where(function ($q) use ($platform) {
                    $q->where('platform', $platform);
                })
                ->first();

            if ($minRule && version_compare($appVersion, $minRule->version, '<')) {
                $latestRule = AppVersion::query()
                    ->where('is_latest', true)
                    ->where('platform', $platform)
                    ->first();

                return response()->json([
                    'success' => false,
                    'message' => "Update Required. Please update your app to version {$minRule->version} or newer to continue.",
                    'code' => 'UPGRADE_REQUIRED',
                    'data' => [
                        'min_version' => $minRule->version,
                        'latest_version' => $latestRule?->version ?? $minRule->version,
                        'force_update' => true,
                        'store_url' => $minRule->store_url ?? $latestRule?->store_url,
                        'release_notes' => $latestRule?->release_notes ?? $minRule->release_notes,
                    ],
                ], 426);
            }

            // If user is authenticated, track latest app version
            if ($user = $request->user()) {
                if ($user->app_version !== $appVersion) {
                    $user->forceFill(['app_version' => $appVersion])->saveQuietly();
                }
            }
        }

        return $next($request);
    }
}
