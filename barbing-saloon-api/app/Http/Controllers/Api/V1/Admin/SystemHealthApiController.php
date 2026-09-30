<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Responses\ApiResponse;
use App\Models\ApiRequestLog;
use App\Models\ErrorEvent;
use App\Models\ErrorGroup;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemHealthApiController
{
    public function overview(): JsonResponse
    {
        $now = Carbon::now();
        $twentyFourHoursAgo = $now->copy()->subHours(24);

        // Errors & Incidents metrics
        $openGroupsCount = ErrorGroup::query()->where('status', 'open')->count();
        $criticalGroupsCount = ErrorGroup::query()->where('status', 'open')->where('severity', 'critical')->count();
        $errorGroupsCount = ErrorGroup::query()->where('status', 'open')->where('severity', 'error')->count();
        $warningGroupsCount = ErrorGroup::query()->where('status', 'open')->where('severity', 'warning')->count();

        $events24hCount = ErrorEvent::query()->where('occurred_at', '>=', $twentyFourHoursAgo)->count();
        $errors5xxCount = ErrorEvent::query()->where('occurred_at', '>=', $twentyFourHoursAgo)->where('http_status', '>=', 500)->count();

        // Top open error groups
        $topErrors = ErrorGroup::query()
            ->where('status', 'open')
            ->orderByDesc('occurrences')
            ->limit(5)
            ->get();

        // Backup overview
        $backupDir = storage_path('app/backups');
        $backupFiles = File::isDirectory($backupDir) ? File::files($backupDir) : [];
        $latestBackupTime = null;
        if (! empty($backupFiles)) {
            $latestMtime = 0;
            foreach ($backupFiles as $f) {
                if ($f->getMTime() > $latestMtime) {
                    $latestMtime = $f->getMTime();
                }
            }
            $latestBackupTime = $latestMtime > 0 ? Carbon::createFromTimestamp($latestMtime)->toIso8601String() : null;
        }

        // Active tokens count
        $activeSessionsCount = 0;
        if (Schema::hasTable('personal_access_tokens')) {
            $activeSessionsCount = DB::table('personal_access_tokens')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->count();
        }

        // Quick DB ping
        $dbOk = true;
        try {
            DB::connection()->getPdo();
        } catch (Throwable) {
            $dbOk = false;
        }

        $overallStatus = (! $dbOk || $criticalGroupsCount > 0) ? 'degraded' : ($openGroupsCount > 5 ? 'attention' : 'optimal');

        return ApiResponse::success([
            'status' => $overallStatus,
            'summary' => [
                'database_connected' => $dbOk,
                'open_groups_count' => $openGroupsCount,
                'critical_groups_count' => $criticalGroupsCount,
                'error_groups_count' => $errorGroupsCount,
                'warning_groups_count' => $warningGroupsCount,
                'events_24h_count' => $events24hCount,
                'errors_5xx_count' => $errors5xxCount,
                'backups_count' => count($backupFiles),
                'latest_backup_time' => $latestBackupTime,
                'active_sessions_count' => $activeSessionsCount,
            ],
            'top_errors' => $topErrors,
            'server_time' => $now->toIso8601String(),
        ], 'System overview retrieved successfully');
    }

    public function healthDetail(): JsonResponse
    {
        $checks = [];

        // 1. Database Diagnostic
        $dbStart = microtime(true);
        $dbStatus = 'disconnected';
        $dbLatency = 0.0;
        $tableCount = 0;
        $connectionName = config('database.default');
        $dbError = null;

        try {
            DB::connection()->getPdo();
            $dbLatency = round((microtime(true) - $dbStart) * 1000, 2);
            $dbStatus = 'connected';

            if ($connectionName === 'mysql') {
                $tables = DB::select('SHOW TABLES');
                $tableCount = count($tables);
            } elseif ($connectionName === 'sqlite') {
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
                $tableCount = count($tables);
            }
        } catch (Throwable $e) {
            $dbStatus = 'error';
            $dbError = $e->getMessage();
        }

        $checks['database'] = [
            'status' => $dbStatus,
            'latency_ms' => $dbLatency,
            'connection' => $connectionName,
            'tables_count' => $tableCount,
            'error' => $dbError,
        ];

        // 2. Cache Diagnostic
        $cacheStart = microtime(true);
        $cacheStatus = 'operational';
        $cacheLatency = 0.0;
        $cacheError = null;

        try {
            $testKey = 'health_check_ping_'.uniqid();
            Cache::put($testKey, 'ok', 10);
            $val = Cache::get($testKey);
            Cache::forget($testKey);
            $cacheLatency = round((microtime(true) - $cacheStart) * 1000, 2);
            if ($val !== 'ok') {
                $cacheStatus = 'degraded';
            }
        } catch (Throwable $e) {
            $cacheStatus = 'error';
            $cacheError = $e->getMessage();
        }

        $checks['cache'] = [
            'status' => $cacheStatus,
            'latency_ms' => $cacheLatency,
            'driver' => config('cache.default'),
            'error' => $cacheError,
        ];

        // 3. Storage & Disks
        $storageDir = storage_path('framework');
        $publicDiskDir = storage_path('app/public');
        $backupDir = storage_path('app/backups');

        $isStorageWritable = is_dir($storageDir) && is_writable($storageDir);
        $isPublicDiskWritable = is_dir($publicDiskDir) && is_writable($publicDiskDir);
        $isBackupWritable = is_dir($backupDir) && is_writable($backupDir);

        $freeBytes = @disk_free_space(storage_path());
        $totalBytes = @disk_total_space(storage_path());
        $freeGb = $freeBytes ? round($freeBytes / 1024 / 1024 / 1024, 2) : null;
        $totalGb = $totalBytes ? round($totalBytes / 1024 / 1024 / 1024, 2) : null;

        $checks['storage'] = [
            'status' => ($isStorageWritable && $isPublicDiskWritable) ? 'operational' : 'error',
            'framework_writable' => $isStorageWritable,
            'public_disk_writable' => $isPublicDiskWritable,
            'backup_dir_writable' => $isBackupWritable,
            'free_space_gb' => $freeGb,
            'total_space_gb' => $totalGb,
        ];

        // 4. Mail Service Configuration
        $mailDriver = config('mail.default');
        $checks['mail'] = [
            'status' => 'configured',
            'driver' => $mailDriver,
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'from_address' => config('mail.from.address'),
        ];

        // 5. Payment Gateways
        $paystackSecret = env('PAYSTACK_SECRET_KEY');
        $stripeSecret = env('STRIPE_SECRET');
        $checks['payments'] = [
            'paystack' => [
                'status' => ! empty($paystackSecret) && ! str_contains($paystackSecret, 'sk_test_placeholder') ? 'configured' : 'sandbox_or_missing',
                'currency' => 'NGN',
            ],
            'stripe' => [
                'status' => ! empty($stripeSecret) ? 'configured' : 'not_configured',
                'currency' => 'USD',
            ],
        ];

        // 6. Queue & Jobs
        $failedJobsCount = 0;
        if (Schema::hasTable('failed_jobs')) {
            $failedJobsCount = DB::table('failed_jobs')->count();
        }

        $checks['queue'] = [
            'status' => $failedJobsCount > 10 ? 'warning' : 'operational',
            'driver' => config('queue.default'),
            'failed_jobs_count' => $failedJobsCount,
        ];

        // 7. Security & Environment
        $checks['environment'] = [
            'app_env' => app()->environment(),
            'debug_mode' => (bool) config('app.debug'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'server_timezone' => date_default_timezone_get(),
            'server_time' => now()->toIso8601String(),
        ];

        $overall = ($dbStatus === 'connected' && $isStorageWritable && $cacheStatus !== 'error') ? 'healthy' : 'degraded';

        return ApiResponse::success([
            'status' => $overall,
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], 'Detailed health report generated');
    }

    public function runSelfTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:all,database,cache,storage',
        ]);

        $results = [];

        if (in_array($validated['type'], ['all', 'database'], true)) {
            $t0 = microtime(true);
            try {
                $res = DB::select('SELECT 1 as ping');
                $ms = round((microtime(true) - $t0) * 1000, 2);
                $results['database'] = [
                    'passed' => true,
                    'latency_ms' => $ms,
                    'message' => 'Database responded successfully to diagnostic query.',
                ];
            } catch (Throwable $e) {
                $results['database'] = [
                    'passed' => false,
                    'error' => $e->getMessage(),
                    'message' => 'Database connection failed.',
                ];
            }
        }

        if (in_array($validated['type'], ['all', 'cache'], true)) {
            $t0 = microtime(true);
            try {
                $key = 'self_test_'.uniqid();
                Cache::put($key, 'candy_probe', 10);
                $read = Cache::get($key);
                Cache::forget($key);
                $ms = round((microtime(true) - $t0) * 1000, 2);
                $results['cache'] = [
                    'passed' => $read === 'candy_probe',
                    'latency_ms' => $ms,
                    'message' => 'Cache atomic read/write/delete succeeded.',
                ];
            } catch (Throwable $e) {
                $results['cache'] = [
                    'passed' => false,
                    'error' => $e->getMessage(),
                    'message' => 'Cache write test failed.',
                ];
            }
        }

        if (in_array($validated['type'], ['all', 'storage'], true)) {
            $t0 = microtime(true);
            try {
                $testPath = storage_path('app/health_probe_'.uniqid().'.tmp');
                File::put($testPath, 'storage_probe_data');
                $read = File::get($testPath);
                File::delete($testPath);
                $ms = round((microtime(true) - $t0) * 1000, 2);
                $results['storage'] = [
                    'passed' => $read === 'storage_probe_data',
                    'latency_ms' => $ms,
                    'message' => 'Filesystem write and delete succeeded.',
                ];
            } catch (Throwable $e) {
                $results['storage'] = [
                    'passed' => false,
                    'error' => $e->getMessage(),
                    'message' => 'Filesystem test failed.',
                ];
            }
        }

        $allPassed = ! in_array(false, array_column($results, 'passed'), true);

        return ApiResponse::success([
            'all_passed' => $allPassed,
            'results' => $results,
            'timestamp' => now()->toIso8601String(),
        ], $allPassed ? 'Diagnostic tests passed' : 'Diagnostic issues detected');
    }

    public function errors(Request $request): JsonResponse
    {
        $query = ErrorGroup::query();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($severity = $request->input('severity')) {
            $query->where('severity', $severity);
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('error_code', 'like', "%{$search}%")
                    ->orWhere('sample_message', 'like', "%{$search}%")
                    ->orWhere('exception_class', 'like', "%{$search}%");
            });
        }

        $errors = $query->orderByDesc('last_seen_at')->paginate(15);

        return ApiResponse::paginated($errors, 'Error groups retrieved successfully');
    }

    public function showError(int $id): JsonResponse
    {
        $group = ErrorGroup::query()->with(['assignee', 'resolvedByUser'])->findOrFail($id);

        $events = ErrorEvent::query()
            ->where('group_id', $id)
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get();

        return ApiResponse::success([
            'group' => $group,
            'events' => $events,
        ], 'Error group details retrieved');
    }

    public function updateError(int $id, Request $request): JsonResponse
    {
        $group = ErrorGroup::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|string|in:open,acknowledged,resolved,ignored',
            'resolution_note' => 'sometimes|string|nullable|max:1000',
        ]);

        if (isset($validated['status'])) {
            $group->status = $validated['status'];
            if ($validated['status'] === 'resolved') {
                $group->resolved_at = now();
                $group->resolved_by = $request->user()?->id;
            } else {
                $group->resolved_at = null;
                $group->resolved_by = null;
            }
        }

        if (array_key_exists('resolution_note', $validated)) {
            $group->resolution_note = $validated['resolution_note'];
        }

        $group->save();

        return ApiResponse::success($group, 'Error status updated successfully');
    }

    public function trace(string $requestId): JsonResponse
    {
        $events = ErrorEvent::query()->where('request_id', $requestId)->get();
        $requestLogs = ApiRequestLog::query()->where('request_id', $requestId)->get();

        return ApiResponse::success([
            'request_id' => $requestId,
            'events' => $events,
            'request_logs' => $requestLogs,
        ], 'Trace data retrieved');
    }
}
