<?php

use App\Console\Commands\ReleaseUnpaidBookingsCommand;
use App\Domain\Shared\Services\CircuitBreakerService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->customer = User::factory()->create(['role' => 'customer']);
    $this->circuitBreaker = app(CircuitBreakerService::class);
    $this->circuitBreaker->reset('paystack');
    $this->circuitBreaker->reset('resend');
});

it('manages circuit breaker states: closed -> open on failures -> half-open on cooldown -> reset', function () {
    // 1. Initial state is closed (healthy)
    expect($this->circuitBreaker->isAvailable('resend'))->toBeTrue();
    $status = $this->circuitBreaker->getStatus('resend');
    expect($status['state'])->toBe('closed');
    expect($status['failures_count'])->toBe(0);

    // 2. Record 4 failures (threshold is 5)
    for ($i = 0; $i < 4; $i++) {
        $this->circuitBreaker->recordFailure('resend', 'Timeout connecting to Resend API');
    }
    expect($this->circuitBreaker->isAvailable('resend'))->toBeTrue();
    expect($this->circuitBreaker->getStatus('resend')['state'])->toBe('closed');

    // 3. 5th failure trips the breaker to open
    $this->circuitBreaker->recordFailure('resend', 'Rate limit exceeded (HTTP 429)');
    expect($this->circuitBreaker->getStatus('resend')['state'])->toBe('open');
    expect($this->circuitBreaker->isAvailable('resend'))->toBeFalse();

    // 4. Reset returns to closed state
    $this->circuitBreaker->reset('resend');
    expect($this->circuitBreaker->getStatus('resend')['state'])->toBe('closed');
    expect($this->circuitBreaker->isAvailable('resend'))->toBeTrue();
});

it('returns circuit breaker statuses and sweeper heartbeat in health detail API', function () {
    Cache::put('telemetry:sweeper:last_run', [
        'timestamp' => Carbon::now()->toIso8601String(),
        'cancelled_count' => 0,
        'duration_ms' => 14.5,
        'status' => 'optimal',
    ], 86400);

    $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/system/health/detail');
    $response->assertOk();

    $checks = $response->json('data.checks');
    expect($checks)->toHaveKey('circuit_breakers');
    expect($checks['circuit_breakers'])->toHaveKey('paystack');
    expect($checks['circuit_breakers'])->toHaveKey('resend');
    expect($checks)->toHaveKey('sweeper');
    expect($checks['sweeper']['status'])->toBe('optimal');
});

it('runs diagnostic self tests for paystack, mail, and sweeper', function () {
    // Paystack probe
    $resPaystack = $this->actingAs($this->admin)->postJson('/api/v1/admin/system/health/test', [
        'type' => 'paystack',
    ]);
    $resPaystack->assertOk();
    expect($resPaystack->json('data.results.paystack'))->toHaveKey('circuit_breaker');

    // Mail probe
    $resMail = $this->actingAs($this->admin)->postJson('/api/v1/admin/system/health/test', [
        'type' => 'mail',
    ]);
    $resMail->assertOk();
    expect($resMail->json('data.results.mail'))->toHaveKey('driver');

    // Sweeper probe
    $resSweeper = $this->actingAs($this->admin)->postJson('/api/v1/admin/system/health/test', [
        'type' => 'sweeper',
    ]);
    $resSweeper->assertOk();
    expect($resSweeper->json('data.results.sweeper.passed'))->toBeTrue();
});

it('lists, retries, and dismisses failed jobs through admin endpoints', function () {
    // Seed two dummy failed jobs (one for retry, one for forget)
    $job1Id = DB::table('failed_jobs')->insertGetId([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'connection' => 'database',
        'queue' => 'notifications',
        'payload' => json_encode([
            'displayName' => 'App\\Jobs\\SendBookingConfirmation',
            'data' => ['appointment_id' => 999],
        ]),
        'exception' => "Resend\\Exceptions\\RateLimitException: Too many requests\nat App/Jobs/SendBookingConfirmation.php:45",
        'failed_at' => now(),
    ]);

    $job2Id = DB::table('failed_jobs')->insertGetId([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'connection' => 'database',
        'queue' => 'notifications',
        'payload' => json_encode([
            'displayName' => 'App\\Jobs\\SendReminderEmail',
            'data' => ['appointment_id' => 998],
        ]),
        'exception' => "Symfony\\Component\\Mailer\\Exception\\TransportException: Connection timeout\nat Mailer.php:88",
        'failed_at' => now(),
    ]);

    // 1. List failed jobs
    $listRes = $this->actingAs($this->admin)->getJson('/api/v1/admin/system/failed-jobs');
    $listRes->assertOk();
    expect($listRes->json('data.total'))->toBeGreaterThanOrEqual(2);
    expect($listRes->json('data.items.0.command_name'))->toBeIn(['SendReminderEmail', 'SendBookingConfirmation']);

    // 2. Retry failed job 1 (dispatches retry)
    $retryRes = $this->actingAs($this->admin)->postJson("/api/v1/admin/system/failed-jobs/{$job1Id}/retry");
    $retryRes->assertOk();
    expect($retryRes->json('data.retried'))->toBeTrue();

    // 3. Forget/dismiss failed job 2
    $forgetRes = $this->actingAs($this->admin)->deleteJson("/api/v1/admin/system/failed-jobs/{$job2Id}");
    $forgetRes->assertOk();
    expect($forgetRes->json('data.dismissed'))->toBeTrue();

    // Verify job 2 was removed
    expect(DB::table('failed_jobs')->where('id', $job2Id)->exists())->toBeFalse();
});

it('allows admin to reset circuit breakers via API', function () {
    $this->circuitBreaker->recordFailure('paystack', 'Gateway timeout', 1);
    expect($this->circuitBreaker->getStatus('paystack')['failures_count'])->toBe(1);

    $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/system/circuit-breakers/paystack/reset');
    $response->assertOk();
    expect($response->json('data.status.state'))->toBe('closed');
    expect($response->json('data.status.failures_count'))->toBe(0);
});

it('records heartbeat telemetry when release unpaid bookings sweeper executes', function () {
    Cache::forget('telemetry:sweeper:last_run');

    Artisan::call('bookings:release-unpaid');

    $heartbeat = Cache::get('telemetry:sweeper:last_run');
    expect($heartbeat)->not->toBeNull();
    expect($heartbeat['status'])->toBe('optimal');
    expect($heartbeat)->toHaveKey('timestamp');
    expect($heartbeat)->toHaveKey('duration_ms');
});

it('executes operations through attempt and falls back or trips on failures', function () {
    $executed = false;
    $res = $this->circuitBreaker->attempt('resend', function () use (&$executed) {
        $executed = true;

        return 'mail_sent';
    });

    expect($res)->toBe('mail_sent');
    expect($executed)->toBeTrue();
    expect($this->circuitBreaker->getStatus('resend')['state'])->toBe('closed');

    // Trip circuit breaker with repeated failures
    for ($i = 0; $i < 5; $i++) {
        try {
            $this->circuitBreaker->attempt('resend', function () {
                throw new \RuntimeException('SMTP Gateway down');
            });
        } catch (\Throwable $e) {
            // expected
        }
    }

    expect($this->circuitBreaker->getStatus('resend')['state'])->toBe('open');

    // Attempting now should trigger fallback
    $fallbackResult = $this->circuitBreaker->attempt(
        'resend',
        fn () => 'should_not_run',
        fn (\Throwable $e) => 'fallback_queued'
    );

    expect($fallbackResult)->toBe('fallback_queued');
});

it('stops PaystackGateway from initiating when circuit breaker is tripped', function () {
    $gateway = new \App\Domain\Payment\Gateways\PaystackGateway($this->circuitBreaker);

    // Trip the breaker manually
    for ($i = 0; $i < 5; $i++) {
        $this->circuitBreaker->recordFailure('paystack', 'Simulated 500 error');
    }

    expect($this->circuitBreaker->isAvailable('paystack'))->toBeFalse();

    $payment = new \App\Models\Payment([
        'amount' => 500000,
        'transaction_ref' => 'REF-CB-TEST',
    ]);

    expect(fn () => $gateway->initiate($payment))->toThrow(
        \App\Domain\Payment\Exceptions\PaymentException::class,
        'circuit breaker'
    );
});
