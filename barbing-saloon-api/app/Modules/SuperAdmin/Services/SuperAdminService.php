<?php

namespace App\Modules\SuperAdmin\Services;

use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class SuperAdminService
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

    public function users(): LengthAwarePaginator
    {
        return User::latest()->paginate(15);
    }

    public function storeUser(array $data): User
    {
        return User::create($data);
    }

    public function updateUser(User $user, array $data): User
    {
        $user->update($data);

        return $user->refresh();
    }

    public function activateUser(User $user): User
    {
        $user->update(['is_active' => true]);

        return $user->refresh();
    }

    public function deactivateUser(User $user): User
    {
        $user->update(['is_active' => false]);

        return $user->refresh();
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }

    public function settings(): array
    {
        return Setting::all()
            ->groupBy('group')
            ->map(fn ($group) => $group->keyBy('key')->map(fn ($item) => $item->value))
            ->all();
    }

    public function updateSettings(array $data): array
    {
        foreach ($data['settings'] as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group']]
            );
        }

        return $this->settings();
    }

    public function auditLogs(): LengthAwarePaginator
    {
        return AuditLog::with('user')->latest()->paginate(20);
    }
}
