<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
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

    public function view(?User $user, BlogPost $blogPost): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        $role = $user->role?->value ?? $user->role;
        return $role === 'barber';
    }

    public function update(User $user, BlogPost $blogPost): bool
    {
        $role = $user->role?->value ?? $user->role;
        return $role === 'barber' && $blogPost->author_id === $user->id;
    }

    public function delete(User $user, BlogPost $blogPost): bool
    {
        $role = $user->role?->value ?? $user->role;
        return $role === 'barber' && $blogPost->author_id === $user->id;
    }
}
