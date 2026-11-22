<?php

declare(strict_types=1);

namespace App\Domain\Admin\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        return [
            'stats' => [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_admins' => User::whereIn('role', ['admin', 'super_admin'])->count(),
                'barbers' => User::where('role', 'barber')->count(),
                'customers' => User::where('role', 'customer')->count(),
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
