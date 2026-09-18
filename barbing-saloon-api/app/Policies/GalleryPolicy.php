<?php

namespace App\Policies;

use App\Models\Gallery;
use App\Models\User;

class GalleryPolicy
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

    public function view(?User $user, Gallery $gallery): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return ($user->role?->value ?? $user->role) === 'barber';
    }

    public function update(User $user, Gallery $gallery): bool
    {
        return ($user->role?->value ?? $user->role) === 'barber' &&
               $gallery->barber_id === $user->barber?->id;
    }

    public function delete(User $user, Gallery $gallery): bool
    {
        return ($user->role?->value ?? $user->role) === 'barber' &&
               $gallery->barber_id === $user->barber?->id;
    }
}
