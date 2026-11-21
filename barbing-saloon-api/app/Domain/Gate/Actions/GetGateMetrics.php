<?php

declare(strict_types=1);

namespace App\Domain\Gate\Actions;

use App\Models\ApiGateLog;
use Illuminate\Support\Facades\DB;

class GetGateMetrics
{
    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $since = now()->subHours(24);

        $baseQuery = ApiGateLog::query()->where('created_at', '>=', $since);

        $totalRequests = (int) $baseQuery->count();

        if ($totalRequests === 0) {
            return [
                'status' => 'operational',
                'window' => '24h',
                'total_requests' => 0,
                'avg_latency_ms' => 0,
                'avg_query_count' => 0.0,
                'slow_requests_count' => 0,
                'budget_warnings_count' => 0,
                'error_count' => 0,
                'error_rate_percent' => 0.0,
                'client_distribution' => [
                    'web' => 0,
                    'mobile' => 0,
                    'webhook' => 0,
                ],
                'top_slow_endpoints' => [],
                'top_queried_endpoints' => [],
            ];
        }

        $avgLatency = (int) round((float) $baseQuery->avg('duration_ms'));
        $avgQueries = (float) round((float) $baseQuery->avg('query_count'), 1);
        $slowCount = (int) (clone $baseQuery)->where('is_slow', true)->count();
        $budgetWarnings = (int) (clone $baseQuery)->where('budget_exceeded', true)->count();
        $errorCount = (int) (clone $baseQuery)->where('status_code', '>=', 400)->count();
        $errorRate = round(($errorCount / $totalRequests) * 100, 1);

        // Client distribution
        $clients = (clone $baseQuery)
            ->select('client_type', DB::raw('count(*) as count'))
            ->groupBy('client_type')
            ->pluck('count', 'client_type')
            ->all();

        // Top 5 slowest endpoints
        $topSlow = DB::table('api_gate_logs')
            ->where('created_at', '>=', $since)
            ->select('method', 'path', DB::raw('ROUND(AVG(duration_ms)) as avg_ms'), DB::raw('COUNT(*) as calls'))
            ->groupBy('method', 'path')
            ->orderByDesc('avg_ms')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'method' => (string) $row->method,
                'path' => (string) $row->path,
                'avg_ms' => (int) $row->avg_ms,
                'calls' => (int) $row->calls,
            ])
            ->all();

        // Top 5 highest query endpoints
        $topQueried = DB::table('api_gate_logs')
            ->where('created_at', '>=', $since)
            ->select('method', 'path', DB::raw('ROUND(AVG(query_count), 1) as avg_queries'), DB::raw('COUNT(*) as calls'))
            ->groupBy('method', 'path')
            ->orderByDesc('avg_queries')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'method' => (string) $row->method,
                'path' => (string) $row->path,
                'avg_queries' => (float) $row->avg_queries,
                'calls' => (int) $row->calls,
            ])
            ->all();

        $status = ($errorRate > 5.0 || $slowCount > ($totalRequests * 0.1)) ? 'degraded' : 'optimal';

        return [
            'status' => $status,
            'window' => '24h',
            'total_requests' => $totalRequests,
            'avg_latency_ms' => $avgLatency,
            'avg_query_count' => $avgQueries,
            'slow_requests_count' => $slowCount,
            'budget_warnings_count' => $budgetWarnings,
            'error_count' => $errorCount,
            'error_rate_percent' => $errorRate,
            'client_distribution' => [
                'web' => (int) ($clients['web'] ?? 0),
                'mobile' => (int) ($clients['mobile'] ?? 0),
                'webhook' => (int) ($clients['webhook'] ?? 0),
            ],
            'top_slow_endpoints' => $topSlow,
            'top_queried_endpoints' => $topQueried,
        ];
    }
}
