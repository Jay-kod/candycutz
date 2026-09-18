<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Gate\Services\DatabaseGateTracker;
use App\Models\ApiGateLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiGateMiddleware
{
    public function __construct(
        protected DatabaseGateTracker $dbTracker
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $this->dbTracker->reset();

        $response = $next($request);

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);
        $queryCount = $this->dbTracker->getQueryCount();
        $queryDurationMs = $this->dbTracker->getQueryDurationMs();

        // Control headers on outgoing response
        $response->headers->set('X-Gate-Status', 'active');
        $response->headers->set('X-Response-Time', "{$durationMs}ms");
        $response->headers->set('X-Query-Count', (string) $queryCount);

        if ($this->dbTracker->isBudgetExceeded()) {
            $response->headers->set('X-Query-Warning', "High query count ({$queryCount} queries)");
        }

        // Store execution telemetry in request attributes for terminate()
        $request->attributes->set('gate_start_time', $startTime);
        $request->attributes->set('gate_duration_ms', $durationMs);
        $request->attributes->set('gate_query_count', $queryCount);
        $request->attributes->set('gate_query_duration_ms', $queryDurationMs);

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            $durationMs = (int) $request->attributes->get('gate_duration_ms', 0);
            $queryCount = (int) $request->attributes->get('gate_query_count', 0);
            $queryDurationMs = (int) $request->attributes->get('gate_query_duration_ms', 0);
            $statusCode = $response->getStatusCode();
            $memoryBytes = memory_get_peak_usage(true);

            $clientType = $this->resolveClientType($request);

            ApiGateLog::create([
                'method' => $request->method(),
                'path' => '/'.ltrim($request->path(), '/'),
                'client_type' => $clientType,
                'status_code' => $statusCode,
                'duration_ms' => $durationMs,
                'query_count' => $queryCount,
                'query_duration_ms' => $queryDurationMs,
                'memory_bytes' => $memoryBytes,
                'user_id' => $request->user()?->id,
                'user_role' => $request->user()?->role?->value ?? null,
                'ip_address' => $request->ip(),
                'is_slow' => $durationMs > 500,
                'budget_exceeded' => $queryCount > DatabaseGateTracker::QUERY_BUDGET_THRESHOLD,
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Failsafe: logging must never impact production traffic
        }
    }

    protected function resolveClientType(Request $request): string
    {
        if ($request->hasHeader('X-Client-Type')) {
            $header = strtolower((string) $request->header('X-Client-Type'));
            if (in_array($header, ['web', 'mobile', 'webhook'], true)) {
                return $header;
            }
        }

        if (str_contains($request->path(), 'payments/webhook')) {
            return 'webhook';
        }

        $ua = strtolower((string) $request->userAgent());
        if (str_contains($ua, 'okhttp') || str_contains($ua, 'cfnetwork') || str_contains($ua, 'expo') || str_contains($ua, 'candycutz-mobile') || str_contains($ua, 'darwin')) {
            return 'mobile';
        }

        return 'web';
    }
}
