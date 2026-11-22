<?php

declare(strict_types=1);

namespace App\Domain\Admin\Services;

use App\Domain\Shared\Services\CircuitBreakerService;
use App\Models\AuditLog;
use App\Models\ErrorGroup;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        $circuitBreaker = app(CircuitBreakerService::class);

        // Database diagnostic latency
        $dbStart = microtime(true);
        $dbStatus = 'operational';
        try {
            DB::selectOne('SELECT 1');
            $dbLatency = round((microtime(true) - $dbStart) * 1000, 2);
        } catch (\Throwable) {
            $dbStatus = 'degraded';
            $dbLatency = 0.0;
        }

        // Sweeper telemetry
        $sweeperData = Cache::get('telemetry:sweeper:last_run');
        $sweeper = [
            'status' => $sweeperData ? ($sweeperData['status'] ?? 'operational') : 'idle',
            'last_run' => $sweeperData['timestamp'] ?? null,
            'cancelled_count' => $sweeperData['cancelled_count'] ?? 0,
            'duration_ms' => $sweeperData['duration_ms'] ?? 0,
        ];

        // Incidents and failed queue jobs
        $openIncidents = Schema::hasTable('error_groups')
            ? ErrorGroup::query()->where('status', 'open')->count()
            : 0;
        $failedJobs = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->count()
            : 0;

        // Blocked malicious probes
        $probesBlocked = (int) Cache::get('security_probes_blocked_total', 0);

        return [
            'stats' => [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_admins' => User::whereIn('role', ['admin', 'super_admin'])->count(),
                'barbers' => User::where('role', 'barber')->count(),
                'customers' => User::where('role', 'customer')->count(),
            ],
            'subsystems' => [
                'database' => [
                    'status' => $dbStatus,
                    'latency_ms' => $dbLatency,
                ],
                'paystack' => $circuitBreaker->getStatus('paystack'),
                'resend' => $circuitBreaker->getStatus('resend'),
                'sweeper' => $sweeper,
                'security' => [
                    'probes_blocked' => $probesBlocked,
                    'status' => 'operational',
                ],
                'incidents' => [
                    'unresolved_count' => $openIncidents,
                    'failed_jobs_count' => $failedJobs,
                    'status' => ($openIncidents > 0 || $failedJobs > 0) ? 'warning' : 'optimal',
                ],
            ],
            'recent_logs' => AuditLog::with('user')->latest()->limit(10)->get(),
        ];
    }

    /**
     * @return LengthAwarePaginator<int, AuditLog>
     */
    public function auditLogs(?string $module = null, ?string $search = null): LengthAwarePaginator
    {
        $query = AuditLog::with('user')->latest();

        if ($module && $module !== 'all') {
            $query->where('module', $module);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate(request()->integer('per_page', 20));
    }
}
