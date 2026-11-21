<?php

declare(strict_types=1);

namespace App\Domain\Gate\Services;

use Illuminate\Support\Facades\DB;

class DatabaseGateTracker
{
    protected int $queryCount = 0;

    protected float $queryDurationMs = 0.0;

    /** @var array<int, array{sql: string, time_ms: float}> */
    protected array $slowQueries = [];

    protected bool $listening = false;

    public const QUERY_BUDGET_THRESHOLD = 25;

    public const SLOW_QUERY_THRESHOLD_MS = 150.0;

    public function bootListener(): void
    {
        if ($this->listening) {
            return;
        }

        $this->listening = true;

        DB::listen(function ($query) {
            $this->queryCount++;
            $this->queryDurationMs += (float) $query->time;

            if ($query->time >= self::SLOW_QUERY_THRESHOLD_MS) {
                $this->slowQueries[] = [
                    'sql' => $query->sql,
                    'time_ms' => (float) $query->time,
                ];
            }
        });
    }

    public function reset(): void
    {
        $this->queryCount = 0;
        $this->queryDurationMs = 0.0;
        $this->slowQueries = [];
    }

    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    public function getQueryDurationMs(): int
    {
        return (int) round($this->queryDurationMs);
    }

    public function isBudgetExceeded(): bool
    {
        return $this->queryCount > self::QUERY_BUDGET_THRESHOLD;
    }

    public function hasSlowQueries(): bool
    {
        return ! empty($this->slowQueries);
    }

    /**
     * @return array<int, array{sql: string, time_ms: float}>
     */
    public function getSlowQueries(): array
    {
        return $this->slowQueries;
    }
}
