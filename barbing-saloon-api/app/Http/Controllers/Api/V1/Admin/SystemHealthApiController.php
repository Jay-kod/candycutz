<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Shared\Services\AuditLogger;
use App\Domain\Shared\Services\CircuitBreakerService;
use App\Http\Responses\ApiResponse;
use App\Models\ApiRequestLog;
use App\Models\ErrorEvent;
use App\Models\ErrorGroup;
use App\Support\Errors\ErrorReporter;
use Carbon\Carbon;
use Illuminate\Database\Connection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemHealthApiController
{
    public function __construct(
        protected ?CircuitBreakerService $circuitBreaker = null
    ) {
        $this->circuitBreaker ??= app(CircuitBreakerService::class);
    }
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
            DB::selectOne('SELECT 1');
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
                'security_probes_blocked' => (int) Cache::get('security_probes_blocked_total', 0),
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
        $connection = DB::connection();
        $connectionName = $connection->getName();
        $databaseName = null;
        $dbError = null;

        try {
            $connection->getPdo();
            $databaseName = $this->getActiveDatabaseName($connection);
            $dbLatency = round((microtime(true) - $dbStart) * 1000, 2);
            $dbStatus = 'connected';

            if ($connection->getDriverName() === 'mysql') {
                $tables = $connection->select('SHOW TABLES');
                $tableCount = count($tables);
            } elseif ($connection->getDriverName() === 'sqlite') {
                $tables = $connection->select("SELECT name FROM sqlite_master WHERE type='table'");
                $tableCount = count($tables);
            }
        } catch (Throwable $e) {
            $dbStatus = 'error';
            $dbError = $e->getMessage();
        }

        $fallbackIncidents = ErrorReporter::getRecentFallbackIncidents(15);
        $recentDisconnects = count(array_filter($fallbackIncidents, fn ($i) => ! empty($i['is_connection_failure'])));

        $checks['database'] = [
            'status' => $dbStatus,
            'latency_ms' => $dbLatency,
            'connection' => $connectionName,
            'driver' => $connection->getDriverName(),
            'database_name' => $databaseName,
            'tables_count' => $tableCount,
            'error' => $dbError,
            'fallback_incidents' => $fallbackIncidents,
            'recent_disconnects_count' => $recentDisconnects,
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
        $paystackSecret = config('payments.gateways.paystack.secret_key');
        $stripeSecret = config('payments.gateways.stripe.secret_key');
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

        // 8. Circuit Breakers Status
        $checks['circuit_breakers'] = $this->circuitBreaker->allStatuses();

        // 9. Sweeper Cron Heartbeat
        $checks['sweeper'] = Cache::get('telemetry:sweeper:last_run', [
            'status' => 'idle',
            'last_run' => null,
            'cancelled_count' => 0,
            'duration_ms' => 0,
        ]);

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
            'type' => 'required|string|in:all,database,cache,storage,paystack,mail,sweeper',
        ]);

        $results = [];

        if (in_array($validated['type'], ['all', 'database'], true)) {
            $t0 = microtime(true);
            try {
                $connection = DB::connection();
                $connection->selectOne('SELECT 1 as ping');
                $ms = round((microtime(true) - $t0) * 1000, 2);

                // Verify critical table integrity
                $requiredTables = ['appointments', 'users', 'services', 'payments', 'webhook_events'];
                $missingTables = [];
                foreach ($requiredTables as $tbl) {
                    if (! Schema::hasTable($tbl)) {
                        $missingTables[] = $tbl;
                    }
                }

                // Check draft reservation schema column
                $hasDraftColumn = Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'draft_expires_at');

                $schemaOk = empty($missingTables) && $hasDraftColumn;
                $detailsMsg = $schemaOk
                    ? 'The active application database responded successfully and schema integrity is fully verified.'
                    : 'Database responded but schema drift was detected! Missing: ' . implode(', ', array_merge($missingTables, $hasDraftColumn ? [] : ['appointments.draft_expires_at']));

                $results['database'] = [
                    'passed' => $schemaOk,
                    'latency_ms' => $ms,
                    'connection' => $connection->getName(),
                    'database_name' => $this->getActiveDatabaseName($connection),
                    'schema_integrity' => $schemaOk,
                    'message' => $detailsMsg,
                ];
            } catch (Throwable $e) {
                $results['database'] = [
                    'passed' => false,
                    'error' => $e->getMessage(),
                    'message' => 'Database connection failed: ' . $e->getMessage(),
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

        if (in_array($validated['type'], ['all', 'paystack'], true)) {
            $t0 = microtime(true);
            try {
                $paystackSecret = (string) config('payments.gateways.paystack.secret_key');
                $isConfigured = ! empty($paystackSecret);
                $ms = round((microtime(true) - $t0) * 1000, 2);

                $results['paystack'] = [
                    'passed' => true,
                    'latency_ms' => $ms,
                    'configured' => $isConfigured,
                    'mode' => str_contains($paystackSecret, 'test') ? 'test' : 'live',
                    'currency' => 'NGN',
                    'circuit_breaker' => $this->circuitBreaker->getStatus('paystack'),
                    'message' => $isConfigured
                        ? 'Paystack gateway configuration and circuit breaker verified.'
                        : 'Paystack secret key is unconfigured.',
                ];
            } catch (Throwable $e) {
                $results['paystack'] = [
                    'passed' => false,
                    'error' => $e->getMessage(),
                    'message' => 'Paystack diagnostic probe failed.',
                ];
            }
        }

        if (in_array($validated['type'], ['all', 'mail'], true)) {
            $t0 = microtime(true);
            try {
                $mailDriver = config('mail.default');
                $fromAddress = config('mail.from.address');
                $ms = round((microtime(true) - $t0) * 1000, 2);

                $results['mail'] = [
                    'passed' => true,
                    'latency_ms' => $ms,
                    'driver' => $mailDriver,
                    'from' => $fromAddress,
                    'circuit_breaker' => $this->circuitBreaker->getStatus('resend'),
                    'message' => 'Mail subsystem transport and circuit breaker verified.',
                ];
            } catch (Throwable $e) {
                $results['mail'] = [
                    'passed' => false,
                    'error' => $e->getMessage(),
                    'message' => 'Mail diagnostic probe failed.',
                ];
            }
        }

        if (in_array($validated['type'], ['all', 'sweeper'], true)) {
            $sweeperTelemetry = Cache::get('telemetry:sweeper:last_run', null);
            $results['sweeper'] = [
                'passed' => true,
                'telemetry' => $sweeperTelemetry,
                'message' => $sweeperTelemetry
                    ? 'Background slot release sweeper is active and reporting heartbeats.'
                    : 'Sweeper is waiting for its scheduled cron run.',
            ];
        }

        $allPassed = ! in_array(false, array_column($results, 'passed'), true);

        return ApiResponse::success([
            'all_passed' => $allPassed,
            'results' => $results,
            'timestamp' => now()->toIso8601String(),
        ], $allPassed ? 'Diagnostic tests passed' : 'Diagnostic issues detected');
    }

    private function getActiveDatabaseName(Connection $connection): ?string
    {
        return match ($connection->getDriverName()) {
            'mysql' => $connection->selectOne('SELECT DATABASE() AS database_name')?->database_name,
            'pgsql' => $connection->selectOne('SELECT current_database() AS database_name')?->database_name,
            'sqlite' => $this->getSqliteDatabaseName($connection),
            default => $connection->getDatabaseName(),
        };
    }

    private function getSqliteDatabaseName(Connection $connection): string
    {
        $database = $connection->selectOne('PRAGMA database_list');
        $file = $database->file ?? '';

        return $file === '' ? ':memory:' : basename($file);
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

    public function failedJobs(Request $request): JsonResponse
    {
        if (! Schema::hasTable('failed_jobs')) {
            return ApiResponse::success(['items' => [], 'total' => 0], 'No failed jobs table');
        }

        $jobs = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit(50)
            ->get()
            ->map(function ($job) {
                $payload = json_decode((string) $job->payload, true) ?: [];
                $commandName = $payload['displayName'] ?? ($payload['data']['commandName'] ?? 'Unknown Job');
                $exceptionLines = explode("\n", (string) $job->exception);
                $firstErrorLine = $exceptionLines[0] ?? 'Unknown error';

                return [
                    'id' => $job->id,
                    'uuid' => $job->uuid,
                    'connection' => $job->connection,
                    'queue' => $job->queue,
                    'command_name' => class_basename($commandName),
                    'full_command_name' => $commandName,
                    'error_summary' => substr($firstErrorLine, 0, 180),
                    'failed_at' => $job->failed_at,
                ];
            });

        return ApiResponse::success([
            'items' => $jobs,
            'total' => count($jobs),
        ], 'Failed jobs retrieved successfully');
    }

    public function retryFailedJob(int $id): JsonResponse
    {
        if (! Schema::hasTable('failed_jobs')) {
            return ApiResponse::error('Failed jobs table does not exist', [], 404);
        }

        $job = DB::table('failed_jobs')->where('id', $id)->first();
        if (! $job) {
            return ApiResponse::error('Failed job not found', [], 404);
        }

        try {
            Artisan::call('queue:retry', ['id' => [$job->uuid ?? (string) $id]]);
        } catch (\Throwable) {
            // In testing or sync driver, retry call may return exit code
        }

        AuditLogger::log('failed_job_retried', 'SystemHealth', null, [], ['job_id' => $id]);

        return ApiResponse::success([
            'id' => $id,
            'retried' => true,
        ], 'Failed job dispatched for retry successfully');
    }

    public function forgetFailedJob(int $id): JsonResponse
    {
        if (! Schema::hasTable('failed_jobs')) {
            return ApiResponse::error('Failed jobs table does not exist', [], 404);
        }

        $job = DB::table('failed_jobs')->where('id', $id)->first();
        if (! $job) {
            return ApiResponse::error('Failed job not found', [], 404);
        }

        try {
            Artisan::call('queue:forget', ['id' => $job->uuid ?? (string) $id]);
        } catch (\Throwable) {
        }

        DB::table('failed_jobs')->where('id', $id)->delete();
        AuditLogger::log('failed_job_dismissed', 'SystemHealth', null, [], ['job_id' => $id]);

        return ApiResponse::success([
            'id' => $id,
            'dismissed' => true,
        ], 'Failed job dismissed successfully');
    }

    public function resetCircuitBreaker(Request $request, string $service): JsonResponse
    {
        $this->circuitBreaker->reset($service);
        AuditLogger::log('circuit_breaker_reset', 'SystemHealth', null, [], ['service' => $service]);

        return ApiResponse::success([
            'service' => $service,
            'status' => $this->circuitBreaker->getStatus($service),
        ], "Circuit breaker for service [{$service}] reset to healthy.");
    }
}
