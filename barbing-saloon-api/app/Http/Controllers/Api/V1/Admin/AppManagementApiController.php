<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Responses\ApiResponse;
use App\Jobs\SendExpoPush;
use App\Models\AppCrash;
use App\Models\AppVersion;
use App\Models\DeviceToken;
use App\Models\PushDeliveryStat;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class AppManagementApiController
{
    /**
     * Overview metrics for mobile app health and version control.
     */
    public function overview(): JsonResponse
    {
        $minVersionAndroid = AppVersion::where('platform', 'android')->where('is_minimum', true)->first();
        $minVersionIos = AppVersion::where('platform', 'ios')->where('is_minimum', true)->first();
        $latestAndroid = AppVersion::where('platform', 'android')->where('is_latest', true)->first();
        $latestIos = AppVersion::where('platform', 'ios')->where('is_latest', true)->first();

        $isMaintenance = Setting::where('key', 'app_maintenance_mode')->value('value') === 'true';
        $maintenanceMessage = Setting::where('key', 'app_maintenance_message')->value('value') ?: '';

        $totalActiveSessions = DB::table('personal_access_tokens')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->count();

        $totalPushTokens = DeviceToken::where('is_revoked', false)->count();
        $totalUsers = User::count();
        $activeUsersWithToken = DeviceToken::where('is_revoked', false)->distinct('user_id')->count('user_id');
        $pushCoveragePct = $totalUsers > 0 ? round(($activeUsersWithToken / $totalUsers) * 100, 1) : 0;

        $unresolvedCrashes = AppCrash::unresolved()->count();

        // Version distribution from users or device tokens
        $versionDistribution = DB::table('device_tokens')
            ->select('app_version', DB::raw('count(*) as count'))
            ->whereNotNull('app_version')
            ->where('is_revoked', false)
            ->groupBy('app_version')
            ->orderByDesc('count')
            ->get();

        return ApiResponse::success([
            'versions' => [
                'android' => [
                    'minimum' => $minVersionAndroid?->version ?? '1.0.0',
                    'latest' => $latestAndroid?->version ?? '1.0.0',
                    'force_update' => (bool) ($minVersionAndroid?->force_update ?? false),
                    'store_url' => $minVersionAndroid?->store_url ?? '',
                ],
                'ios' => [
                    'minimum' => $minVersionIos?->version ?? '1.0.0',
                    'latest' => $latestIos?->version ?? '1.0.0',
                    'force_update' => (bool) ($minVersionIos?->force_update ?? false),
                    'store_url' => $minVersionIos?->store_url ?? '',
                ],
            ],
            'maintenance' => [
                'enabled' => $isMaintenance,
                'message' => $maintenanceMessage,
            ],
            'metrics' => [
                'active_sessions' => $totalActiveSessions,
                'push_tokens' => $totalPushTokens,
                'push_coverage_pct' => $pushCoveragePct,
                'unresolved_crashes' => $unresolvedCrashes,
            ],
            'version_distribution' => $versionDistribution,
        ], 'App overview metrics loaded.');
    }

    /**
     * List all defined app versions.
     */
    public function versions(): JsonResponse
    {
        $versions = AppVersion::orderByDesc('created_at')->get();

        return ApiResponse::success($versions, 'App versions retrieved.');
    }

    /**
     * Create or update an app version rule.
     */
    public function storeVersion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'version' => 'required|string|max:32',
            'platform' => 'required|string|in:ios,android',
            'is_minimum' => 'boolean',
            'is_latest' => 'boolean',
            'force_update' => 'boolean',
            'release_notes' => 'nullable|string|max:2000',
            'store_url' => 'nullable|string|url|max:500',
        ]);

        if (! empty($validated['is_minimum'])) {
            AppVersion::where('platform', $validated['platform'])->update(['is_minimum' => false]);
        }

        if (! empty($validated['is_latest'])) {
            AppVersion::where('platform', $validated['platform'])->update(['is_latest' => false]);
        }

        $appVersion = AppVersion::updateOrCreate(
            ['version' => $validated['version'], 'platform' => $validated['platform']],
            [
                'is_minimum' => $validated['is_minimum'] ?? false,
                'is_latest' => $validated['is_latest'] ?? false,
                'force_update' => $validated['force_update'] ?? false,
                'release_notes' => $validated['release_notes'] ?? null,
                'store_url' => $validated['store_url'] ?? null,
                'released_at' => now(),
            ]
        );

        return ApiResponse::success($appVersion, 'App version configured successfully.');
    }

    /**
     * List active Sanctum bearer tokens with device and user info.
     */
    public function sessions(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $role = $request->query('role');

        $query = PersonalAccessToken::query()
            ->with(['tokenable' => function ($q) {
                $q->select('id', 'name', 'email', 'role', 'avatar', 'phone');
            }])
            ->orderByDesc('last_used_at');

        if ($role) {
            $query->whereHasMorph('tokenable', [User::class], function ($q) use ($role) {
                $q->where('role', $role);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHasMorph('tokenable', [User::class], function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $sessions = $query->paginate($perPage);

        return ApiResponse::success($sessions, 'Active device sessions retrieved.');
    }

    /**
     * Revoke a single session token remotely.
     */
    public function revokeSession(int $id): JsonResponse
    {
        $token = PersonalAccessToken::findOrFail($id);
        $token->delete();

        return ApiResponse::success(null, 'Session token revoked successfully.');
    }

    /**
     * Revoke all session tokens for a user.
     */
    public function revokeUserSessions(int $userId): JsonResponse
    {
        PersonalAccessToken::where('tokenable_type', User::class)
            ->where('tokenable_id', $userId)
            ->delete();

        return ApiResponse::success(null, 'All sessions for the user have been revoked.');
    }

    /**
     * Push notification delivery health metrics.
     */
    public function pushHealth(): JsonResponse
    {
        $totalUsers = User::count();
        $totalTokens = DeviceToken::where('is_revoked', false)->count();
        $usersWithTokens = DeviceToken::where('is_revoked', false)->distinct('user_id')->count('user_id');

        $platformBreakdown = DeviceToken::where('is_revoked', false)
            ->select('platform', DB::raw('count(*) as count'))
            ->groupBy('platform')
            ->get();

        $revokedCount = DeviceToken::where('is_revoked', true)->count();

        // 14 day history
        $history = PushDeliveryStat::query()
            ->orderByDesc('date')
            ->limit(14)
            ->get()
            ->reverse()
            ->values();

        // Aggregate failures
        $aggregatedReasons = [];
        foreach ($history as $item) {
            if (is_array($item->failure_reasons)) {
                foreach ($item->failure_reasons as $reason => $count) {
                    $aggregatedReasons[$reason] = ($aggregatedReasons[$reason] ?? 0) + $count;
                }
            }
        }

        return ApiResponse::success([
            'total_users' => $totalUsers,
            'registered_users_count' => $usersWithTokens,
            'coverage_percentage' => $totalUsers > 0 ? round(($usersWithTokens / $totalUsers) * 100, 1) : 0,
            'active_tokens_count' => $totalTokens,
            'revoked_tokens_count' => $revokedCount,
            'platform_breakdown' => $platformBreakdown,
            'history' => $history,
            'common_failure_reasons' => $aggregatedReasons,
        ], 'Push notification health data retrieved.');
    }

    /**
     * Send a test push notification to verify delivery pipeline.
     */
    public function pushTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'nullable|string',
            'user_id' => 'nullable|integer|exists:users,id',
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:500',
        ]);

        $tokens = [];
        if (! empty($validated['token'])) {
            $tokens[] = $validated['token'];
        } elseif (! empty($validated['user_id'])) {
            $tokens = DeviceToken::where('user_id', $validated['user_id'])
                ->where('is_revoked', false)
                ->pluck('token')
                ->all();
        }

        if (empty($tokens)) {
            return ApiResponse::error('No valid push tokens found to send test notification.', [], 422);
        }

        SendExpoPush::dispatch($tokens, $validated['title'], $validated['body'], ['test' => true]);

        return ApiResponse::success([
            'dispatched_token_count' => count($tokens),
        ], 'Test push notification queued for dispatch.');
    }

    /**
     * App crash telemetry list.
     */
    public function crashes(Request $request): JsonResponse
    {
        $status = $request->query('status'); // unresolved, resolved
        $search = $request->query('search');

        $query = AppCrash::with(['user:id,name,email,role'])
            ->orderByDesc('created_at');

        if ($status === 'unresolved') {
            $query->unresolved();
        } elseif ($status === 'resolved') {
            $query->resolved();
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('error_message', 'like', "%{$search}%")
                    ->orWhere('platform', 'like', "%{$search}%")
                    ->orWhere('app_version', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $crashes = $query->paginate($perPage);

        return ApiResponse::success($crashes, 'App crashes retrieved.');
    }

    /**
     * Mark a crash report as resolved.
     */
    public function resolveCrash(int $id): JsonResponse
    {
        $crash = AppCrash::findOrFail($id);
        $crash->resolved_at = now();
        $crash->save();

        return ApiResponse::success($crash, 'Crash marked as resolved.');
    }

    /**
     * Toggle soft app maintenance mode in settings.
     */
    public function toggleMaintenance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'message' => 'nullable|string|max:500',
        ]);

        Setting::updateOrCreate(
            ['key' => 'app_maintenance_mode'],
            ['value' => $validated['enabled'] ? 'true' : 'false', 'group' => 'mobile']
        );

        if (isset($validated['message'])) {
            Setting::updateOrCreate(
                ['key' => 'app_maintenance_message'],
                ['value' => $validated['message'], 'group' => 'mobile']
            );
        }

        return ApiResponse::success([
            'app_maintenance_mode' => $validated['enabled'],
            'app_maintenance_message' => $validated['message'] ?? '',
        ], 'App maintenance mode settings updated.');
    }
}
