<?php

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;

class TestimonialPolicy
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

    public function view(?User $user, Testimonial $testimonial): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        return false;
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        return false;
    }
}
