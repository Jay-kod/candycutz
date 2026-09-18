<?php

namespace App\Policies;

use App\Models\Barber;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BarberPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if (in_array($user->role?->value ?? $user->role, ['admin', 'super_admin'])) {
            return true;
        }

        return null;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Barber $barber): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Barber $barber): bool
    {
        // Barbers can update their own profile, but admin/super_admin is handled in before()
        return ($user->role?->value ?? $user->role) === 'barber' && 
               $barber->user_id === $user->id;
    }

    public function delete(User $user, Barber $barber): bool
    {
        return false;
    }
}
