<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (in_array($user->role?->value ?? $user->role, ['super_admin', 'admin'])) {
            return true;
        }

        return null;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Service $service): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false; // Handled by before() for admins
    }

    public function update(User $user, Service $service): bool
    {
        return false; // Handled by before() for admins
    }

    public function delete(User $user, Service $service): bool
    {
        return false; // Handled by before() for admins
    }
}
