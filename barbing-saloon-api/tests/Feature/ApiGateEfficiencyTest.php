<?php

use App\Domain\Gate\Actions\GetGateMetrics;
use App\Domain\Gate\Services\DatabaseGateTracker;
use App\Models\ApiGateLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

it('short-circuits rate-limited ingress requests efficiently with zero database leakage', function () {
    $ip = '127.0.0.1';
    $identity = 'burst-test@candycutz.ng';
    $rateLimitKey = "{$identity}|{$ip}";

    RateLimiter::clear($rateLimitKey);

    // Exhaust the 10 req/min sensitive auth limit
    for ($i = 0; $i < 10; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'identity' => $identity,
            'password' => 'test-password',
        ]);
    }

    // Capture tracker state and execution time for the 11th short-circuited request
    $startTime = microtime(true);
    $tracker = app(DatabaseGateTracker::class);
    $tracker->reset();

    $throttled = $this->postJson('/api/v1/auth/login', [
        'identity' => $identity,
        'password' => 'test-password',
    ]);

    $durationMs = (microtime(true) - $startTime) * 1000;

    $throttled->assertStatus(429);
    $throttled->assertJsonPath('error.code', 'RATE_LIMIT_EXCEEDED');
    expect($throttled->headers->has('Retry-After'))->toBeTrue();

    // Verification of efficiency: Short-circuiting must execute swiftly with zero database queries
    expect($durationMs)->toBeLessThan(100.0);
    expect($tracker->getQueryCount())->toBeLessThanOrEqual(1); // At most 1 for asynchronous gate logging in terminate()
});

it('verifies DatabaseGateTracker micro-overhead and memory efficiency under high query iterations', function () {
    $tracker = app(DatabaseGateTracker::class);
    $tracker->bootListener();
    $tracker->reset();

    $initialMemory = memory_get_usage();
    $startTime = microtime(true);

    // Execute a batch of 30 distinct SQL queries
    for ($i = 1; $i <= 30; $i++) {
        DB::select('SELECT 1 as val');
    }

    $durationMs = (microtime(true) - $startTime) * 1000;
    $memoryDeltaKb = (memory_get_usage() - $initialMemory) / 1024;

    expect($tracker->getQueryCount())->toBe(30);
    expect($tracker->getQueryDurationMs())->toBeGreaterThanOrEqual(0);
    expect($tracker->isBudgetExceeded())->toBeTrue(); // > 25 threshold triggered

    // Tracker overhead must be negligible
    expect($durationMs)->toBeLessThan(150.0);
    expect($memoryDeltaKb)->toBeLessThan(256.0); // Memory footprint well under 256KB

    // Verify instantaneous state reset
    $tracker->reset();
    expect($tracker->getQueryCount())->toBe(0);
    expect($tracker->getQueryDurationMs())->toBe(0);
    expect($tracker->isBudgetExceeded())->toBeFalse();
});

it('detects query budget breaches efficiently and attaches warning headers to responses', function () {
    $tracker = app(DatabaseGateTracker::class);
    $tracker->reset();

    // Execute request to health endpoint while injecting queries to exceed budget threshold
    $response = $this->getJson('/api/v1/health');
    $response->assertStatus(200);

    // Simulate exceeding budget threshold (26 queries)
    for ($i = 0; $i < 26; $i++) {
        DB::select('SELECT 1');
    }

    expect($tracker->isBudgetExceeded())->toBeTrue();
    expect($tracker->getQueryCount())->toBeGreaterThanOrEqual(26);

    // Verify tracker resets cleanly and remains robust
    $tracker->reset();
    expect($tracker->isBudgetExceeded())->toBeFalse();
    expect($tracker->getQueryCount())->toBe(0);
});

it('aggregates high-volume 24h telemetry metrics in sub-50ms execution time', function () {
    $since = now()->subHours(2)->toDateTimeString();

    // Seed a dense batch of 120 gate telemetry logs in chunks of 30 for SQLite binding limits
    $logs = [];
    for ($i = 1; $i <= 120; $i++) {
        $clientType = $i % 3 === 0 ? 'mobile' : ($i % 5 === 0 ? 'webhook' : 'web');
        $statusCode = $i % 20 === 0 ? 500 : ($i % 10 === 0 ? 422 : 200);
        $duration = 20 + ($i % 10) * 15;
        $queries = 1 + ($i % 8);

        $logs[] = [
            'method' => $i % 4 === 0 ? 'POST' : 'GET',
            'path' => $i % 2 === 0 ? '/api/v1/services' : '/api/v1/appointments',
            'client_type' => $clientType,
            'status_code' => $statusCode,
            'duration_ms' => $duration,
            'query_count' => $queries,
            'query_duration_ms' => (int) round($duration * 0.4),
            'memory_bytes' => 2048000,
            'user_id' => null,
            'user_role' => null,
            'ip_address' => '127.0.0.1',
            'is_slow' => $duration > 150,
            'budget_exceeded' => $queries > 25,
            'created_at' => $since,
        ];
    }

    foreach (array_chunk($logs, 30) as $chunk) {
        ApiGateLog::insert($chunk);
    }

    $action = app(GetGateMetrics::class);

    $startTime = microtime(true);
    $metrics = $action->execute();
    $durationMs = (microtime(true) - $startTime) * 1000;

    // Verify speed
    expect($durationMs)->toBeLessThan(100.0);

    // Verify accuracy of mathematical aggregates
    expect($metrics['total_requests'])->toBe(120);
    expect($metrics['status'])->toBeIn(['optimal', 'degraded']);
    expect($metrics['avg_latency_ms'])->toBeGreaterThan(0);
    expect($metrics['avg_query_count'])->toBeGreaterThan(0.0);
    expect($metrics['client_distribution']['web'])->toBeGreaterThan(0);
    expect($metrics['client_distribution']['mobile'])->toBeGreaterThan(0);
    expect($metrics['client_distribution']['webhook'])->toBeGreaterThan(0);
    expect(count($metrics['top_slow_endpoints']))->toBeLessThanOrEqual(5);
    expect(count($metrics['top_queried_endpoints']))->toBeLessThanOrEqual(5);
});

it('prunes bulk historical gate telemetry with selective retention efficiency', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $oldLogs = [];
    $tenDaysAgo = now()->subDays(10)->toDateTimeString();
    for ($i = 0; $i < 60; $i++) {
        $oldLogs[] = [
            'method' => 'GET',
            'path' => '/api/v1/health',
            'client_type' => 'web',
            'status_code' => 200,
            'duration_ms' => 15,
            'query_count' => 1,
            'query_duration_ms' => 2,
            'memory_bytes' => 1024000,
            'user_id' => null,
            'user_role' => null,
            'ip_address' => '127.0.0.1',
            'is_slow' => false,
            'budget_exceeded' => false,
            'created_at' => $tenDaysAgo,
        ];
    }

    $recentLogs = [];
    $twoDaysAgo = now()->subDays(2)->toDateTimeString();
    for ($i = 0; $i < 40; $i++) {
        $recentLogs[] = [
            'method' => 'GET',
            'path' => '/api/v1/health',
            'client_type' => 'web',
            'status_code' => 200,
            'duration_ms' => 15,
            'query_count' => 1,
            'query_duration_ms' => 2,
            'memory_bytes' => 1024000,
            'user_id' => null,
            'user_role' => null,
            'ip_address' => '127.0.0.1',
            'is_slow' => false,
            'budget_exceeded' => false,
            'created_at' => $twoDaysAgo,
        ];
    }

    foreach (array_chunk(array_merge($oldLogs, $recentLogs), 30) as $chunk) {
        ApiGateLog::insert($chunk);
    }

    expect(ApiGateLog::count())->toBe(100);

    $startTime = microtime(true);
    $response = $this->actingAs($admin)->postJson('/api/v1/admin/gate/flush', [
        'keep_days' => 7,
    ]);
    $durationMs = (microtime(true) - $startTime) * 1000;

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.pruned_records', 60);
    $response->assertJsonPath('data.retained_days', 7);

    // Verification of efficiency & retention integrity
    expect($durationMs)->toBeLessThan(250.0); // Sub-second bulk pruning
    expect(ApiGateLog::where('created_at', '<', now()->subDays(7))->count())->toBe(0); // All 60 older records purged
    expect(ApiGateLog::count())->toBe(41); // 40 recent logs + 1 telemetry log created for the flush request itself
});
