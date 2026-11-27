<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Shared\Services\CircuitBreakerService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SuperAdminDashboardTelemetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_retrieve_live_subsystem_telemetry_and_stats(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'email' => 'chief@candycutz.internal']);

        $response = $this->actingAs($superAdmin)->getJson('/api/v1/super-admin/dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'stats' => [
                    'total_users',
                    'active_users',
                    'total_admins',
                    'barbers',
                    'customers',
                ],
                'subsystems' => [
                    'database' => [
                        'status',
                        'latency_ms',
                    ],
                    'paystack' => [
                        'service',
                        'state',
                        'failures_count',
                        'threshold',
                        'is_available',
                    ],
                    'resend' => [
                        'service',
                        'state',
                        'failures_count',
                        'threshold',
                        'is_available',
                    ],
                    'sweeper' => [
                        'status',
                        'last_run',
                        'cancelled_count',
                        'duration_ms',
                    ],
                    'security' => [
                        'probes_blocked',
                        'status',
                    ],
                    'incidents' => [
                        'unresolved_count',
                        'failed_jobs_count',
                        'status',
                    ],
                ],
                'recent_logs',
            ],
            'message',
        ]);

        $data = $response->json('data');
        expect($data['subsystems']['database']['status'])->toBe('operational');
        expect($data['subsystems']['paystack']['state'])->toBe('closed');
        expect($data['subsystems']['resend']['state'])->toBe('closed');
    }

    public function test_non_superadmin_is_forbidden_from_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);

        $res1 = $this->actingAs($customer)->getJson('/api/v1/super-admin/dashboard');
        $res1->assertStatus(403);

        $res2 = $this->actingAs($admin)->getJson('/api/v1/super-admin/dashboard');
        $res2->assertStatus(403);
    }

    public function test_subsystem_telemetry_reflects_circuit_breaker_trips_and_sweeper_heartbeats(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        // 1. Simulate sweeper cron run
        Cache::put('telemetry:sweeper:last_run', [
            'timestamp' => now()->toIso8601String(),
            'cancelled_count' => 3,
            'duration_ms' => 14.5,
            'status' => 'optimal',
        ], 86400);

        // 2. Simulate security probes blocked
        Cache::put('security_probes_blocked_total', 42);

        // 3. Trip Paystack circuit breaker
        /** @var CircuitBreakerService $cb */
        $cb = app(CircuitBreakerService::class);
        for ($i = 0; $i < 5; $i++) {
            $cb->recordFailure('paystack', 'Simulated gateway timeout');
        }

        $response = $this->actingAs($superAdmin)->getJson('/api/v1/super-admin/dashboard');
        $response->assertStatus(200);

        $subsystems = $response->json('data.subsystems');
        expect($subsystems['sweeper']['status'])->toBe('optimal');
        expect($subsystems['sweeper']['cancelled_count'])->toBe(3);
        expect($subsystems['security']['probes_blocked'])->toBe(42);
        expect($subsystems['paystack']['state'])->toBe('open');
        expect($subsystems['paystack']['failures_count'])->toBe(5);
        expect($subsystems['paystack']['is_available'])->toBeFalse();
    }
}
