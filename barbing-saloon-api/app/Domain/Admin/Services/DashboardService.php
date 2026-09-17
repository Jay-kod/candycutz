<?php

declare(strict_types=1);

namespace App\Domain\Admin\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardService
{
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

    public function auditLogs(): LengthAwarePaginator
    {
        return AuditLog::with('user')->latest()->paginate(20);
    }
}