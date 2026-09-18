<?php

declare(strict_types=1);

namespace App\Domain\Gate\Actions;

use App\Models\ApiGateLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetGateLogs
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<ApiGateLog>
     */
    public function execute(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = ApiGateLog::query()->with('user:id,name,role')->latest('id');

        if (! empty($filters['client_type']) && $filters['client_type'] !== 'all') {
            $query->where('client_type', $filters['client_type']);
        }

        if (isset($filters['is_slow']) && $filters['is_slow'] !== '') {
            $query->where('is_slow', filter_var($filters['is_slow'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['budget_exceeded']) && $filters['budget_exceeded'] !== '') {
            $query->where('budget_exceeded', filter_var($filters['budget_exceeded'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['search'])) {
            $search = (string) $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('path', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('method', 'like', "%{$search}%");
            });
        }

        return $query->paginate(min($perPage, 100));
    }
}
