<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ServiceCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceCategoryPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if (in_array($user->role?->value ?? $user->role, ['admin', 'super_admin'])) {
            return true;
        }

        return null; // Fall through to other checks
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, ServiceCategory $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ServiceCategory $category): bool
    {
        return false;
    }

    public function delete(User $user, ServiceCategory $category): bool
    {
        return false;
    }
}
