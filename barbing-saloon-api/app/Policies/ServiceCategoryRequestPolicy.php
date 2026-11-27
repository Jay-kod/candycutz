<?php

namespace App\Policies;

use App\Models\ServiceCategoryRequest;
use App\Models\User;

class ServiceCategoryRequestPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (in_array($user->role->value ?? $user->role, ['admin', 'super_admin'], true)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return ($user->role->value ?? $user->role) === 'barber';
    }

    public function view(User $user, ServiceCategoryRequest $categoryRequest): bool
    {
        return ($user->role->value ?? $user->role) === 'barber'
            && $categoryRequest->requested_by === $user->id;
    }

    public function create(User $user): bool
    {
        return ($user->role->value ?? $user->role) === 'barber';
    }

    public function review(User $user, ServiceCategoryRequest $categoryRequest): bool
    {
        return false;
    }
}
