<?php

declare(strict_types=1);

namespace App\Domain\Shared\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CircuitBreakerService
{
    protected const DEFAULT_THRESHOLD = 5;
    protected const DEFAULT_COOLDOWN_SECONDS = 60;

    /**
     * @var array<int, string>
     */
    protected array $registeredServices = [
        'paystack',
        'resend',
        'database',
        'storage',
    ];

    /**
     * Check if a subsystem is currently available to process tasks.
     *
     * State machine:
     * - closed: healthy, allows execution
     * - open: tripped/failing, rejects tasks until cooldown expires
     * - half_open: testing recovery via single probe
     */
    public function isAvailable(string $service): bool
    {
        $status = $this->getStatus($service);

        if ($status['state'] === 'closed') {
            return true;
        }

        if ($status['state'] === 'half_open') {
            return true;
        }

        // Open state: check if cooldown has elapsed
        if ($status['cooldown_seconds_remaining'] <= 0) {
            $this->transitionTo($service, 'half_open');

            return true;
        }

        return false;
    }

    /**
     * Safely attempt an operation through the circuit breaker.
     *
     * @template T
     * @param callable(): T $operation
     * @param (callable(\Throwable): T)|null $fallback
     * @return T
     * @throws \Throwable
     */
    public function attempt(string $service, callable $operation, ?callable $fallback = null): mixed
    {
        if (! $this->isAvailable($service)) {
            $status = $this->getStatus($service);
            $msg = "Circuit breaker for {$service} is currently OPEN ({$status['cooldown_seconds_remaining']}s cooldown remaining).";
            if ($fallback !== null) {
                return $fallback(new \RuntimeException($msg));
            }
            throw new \RuntimeException($msg);
        }

        try {
            $result = $operation();
            $this->recordSuccess($service);

            return $result;
        } catch (\Throwable $e) {
            $this->recordFailure($service, $e->getMessage());
            if ($fallback !== null) {
                return $fallback($e);
            }
            throw $e;
        }
    }

    /**
     * Record a successful operation for a subsystem.
     */
    public function recordSuccess(string $service): void
    {
        $stateKey = $this->key($service, 'state');
        $failuresKey = $this->key($service, 'failures');

        Cache::forget($failuresKey);
        Cache::put($stateKey, 'closed', 86400);
    }

    /**
     * Record a failed operation for a subsystem.
     */
    public function recordFailure(string $service, ?string $reason = null, int $threshold = self::DEFAULT_THRESHOLD): void
    {
        $failuresKey = $this->key($service, 'failures');
        $failures = (int) Cache::get($failuresKey, 0) + 1;
        Cache::put($failuresKey, $failures, 3600);

        $lastFailKey = $this->key($service, 'last_failure');
        Cache::put($lastFailKey, [
            'timestamp' => Carbon::now()->toIso8601String(),
            'reason' => $reason ?: 'Unknown subsystem error',
        ], 86400);

        if ($failures >= $threshold) {
            $this->transitionTo($service, 'open');
        }
    }

    /**
     * Reset circuit breaker to healthy/closed state.
     */
    public function reset(string $service): void
    {
        Cache::forget($this->key($service, 'failures'));
        Cache::forget($this->key($service, 'last_failure'));
        Cache::forget($this->key($service, 'tripped_at'));
        Cache::put($this->key($service, 'state'), 'closed', 86400);
    }

    /**
     * Get detailed status of a specific subsystem circuit breaker.
     *
     * @return array<string, mixed>
     */
    public function getStatus(string $service): array
    {
        $state = (string) Cache::get($this->key($service, 'state'), 'closed');
        $failures = (int) Cache::get($this->key($service, 'failures'), 0);
        $lastFailure = Cache::get($this->key($service, 'last_failure'), null);
        $trippedAt = Cache::get($this->key($service, 'tripped_at'), null);

        $cooldownRemaining = 0;
        if ($state === 'open' && $trippedAt) {
            $trippedCarbon = Carbon::parse($trippedAt);
            $elapsed = (int) Carbon::now()->diffInSeconds($trippedCarbon, false);
            $cooldownRemaining = max(0, self::DEFAULT_COOLDOWN_SECONDS - abs($elapsed));

            if ($cooldownRemaining === 0) {
                $state = 'half_open';
            }
        }

        return [
            'service' => $service,
            'state' => $state,
            'is_available' => in_array($state, ['closed', 'half_open'], true),
            'failures_count' => $failures,
            'threshold' => self::DEFAULT_THRESHOLD,
            'last_failure' => $lastFailure,
            'cooldown_seconds_remaining' => $cooldownRemaining,
        ];
    }

    /**
     * Get status report for all registered subsystems.
     *
     * @return array<string, array<string, mixed>>
     */
    public function allStatuses(): array
    {
        $report = [];
        foreach ($this->registeredServices as $service) {
            $report[$service] = $this->getStatus($service);
        }

        return $report;
    }

    protected function transitionTo(string $service, string $newState): void
    {
        Cache::put($this->key($service, 'state'), $newState, 86400);

        if ($newState === 'open') {
            Cache::put($this->key($service, 'tripped_at'), Carbon::now()->toIso8601String(), 86400);
        }
    }

    protected function key(string $service, string $suffix): string
    {
        return "circuit_breaker:{$service}:{$suffix}";
    }
}
