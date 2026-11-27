<?php

namespace App\Policies;

use App\Models\GalleryCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GalleryCategoryPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if (in_array($user->role->value ?? $user->role, ['admin', 'super_admin'], true)) {
            return true;
        }

        return null;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, GalleryCategory $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, GalleryCategory $category): bool
    {
        return false;
    }

    public function delete(User $user, GalleryCategory $category): bool
    {
        return false;
    }
}
