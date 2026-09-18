<?php

use App\Models\ApiGateLog;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

it('injects API gate telemetry headers into responses', function () {
    $response = $this->getJson('/api/v1/health');

    $response->assertStatus(200);
    $response->assertHeader('X-Gate-Status', 'active');
    expect($response->headers->has('X-Response-Time'))->toBeTrue();
    expect($response->headers->has('X-Query-Count'))->toBeTrue();
});

it('tracks database queries through the gate tracker during request lifecycle', function () {
    Service::factory()->count(3)->create(['is_active' => true]);

    $response = $this->getJson('/api/v1/services');

    $response->assertStatus(200);
    $queryCount = (int) $response->headers->get('X-Query-Count');
    expect($queryCount)->toBeGreaterThanOrEqual(1);
});

it('throttles sensitive authentication requests after threshold is exceeded', function () {
    RateLimiter::clear('test@candycutz.ng|127.0.0.1');

    for ($i = 0; $i < 10; $i++) {
        $res = $this->postJson('/api/v1/auth/login', [
            'identity' => 'test@candycutz.ng',
            'password' => 'wrong-password',
        ]);
        expect(in_array($res->status(), [401, 422]))->toBeTrue();
    }

    // 11th attempt should trigger 429 Rate Limit Exceeded
    $throttled = $this->postJson('/api/v1/auth/login', [
        'identity' => 'test@candycutz.ng',
        'password' => 'wrong-password',
    ]);

    $throttled->assertStatus(429);
    $throttled->assertJsonPath('error.code', 'RATE_LIMIT_EXCEEDED');
});

it('allows admin to retrieve live gate metrics', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    ApiGateLog::factory()->count(5)->create();

    $response = $this->actingAs($admin)->getJson('/api/v1/admin/gate/metrics');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonStructure([
        'data' => [
            'status',
            'window',
            'total_requests',
            'avg_latency_ms',
            'avg_query_count',
            'slow_requests_count',
            'budget_warnings_count',
            'error_count',
            'error_rate_percent',
            'client_distribution' => ['web', 'mobile', 'webhook'],
            'top_slow_endpoints',
            'top_queried_endpoints',
        ],
    ]);
});

it('allows admin to query gate logs with filters', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    ApiGateLog::factory()->create([
        'client_type' => 'mobile',
        'path' => '/api/v1/mobile-test',
        'is_slow' => true,
    ]);

    $response = $this->actingAs($admin)->getJson('/api/v1/admin/gate/logs?client_type=mobile&is_slow=true');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $items = $response->json('data.items');
    expect(count($items))->toBeGreaterThanOrEqual(1);
    expect($items[0]['client_type'])->toBe('mobile');
    expect($items[0]['is_slow'])->toBeTrue();
});

it('allows admin to flush gate cache and prune old logs', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    ApiGateLog::factory()->create([
        'created_at' => now()->subDays(10),
    ]);

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/gate/flush', ['keep_days' => 7]);

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    expect($response->json('data.pruned_records'))->toBeGreaterThanOrEqual(1);
});

it('forbids customers from accessing gate telemetry', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $response = $this->actingAs($customer)->getJson('/api/v1/admin/gate/metrics');

    $response->assertStatus(403);
});
