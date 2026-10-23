<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        $role = $user->role?->value ?? $user->role;
        if ($role === 'super_admin') {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role?->value ?? $user->role, ['admin', 'super_admin'], true);
    }

    public function view(User $user, Setting $setting): bool
    {
        return in_array($user->role?->value ?? $user->role, ['admin', 'super_admin'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role?->value ?? $user->role, ['admin', 'super_admin'], true);
    }

    public function update(User $user, ?Setting $setting = null): bool
    {
        return in_array($user->role?->value ?? $user->role, ['admin', 'super_admin'], true);
    }

    public function manage(User $user): bool
    {
        return in_array($user->role?->value ?? $user->role, ['admin', 'super_admin'], true);
    }
}
