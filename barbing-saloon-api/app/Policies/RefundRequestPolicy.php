<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RefundRequest;
use App\Models\User;

class RefundRequestPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        $role = $user->role instanceof \BackedEnum ? (string) $user->role->value : (string) $user->role;

        if (in_array($role, ['admin', 'super_admin'], true)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, RefundRequest $refundRequest): bool
    {
        return (int) $refundRequest->customer_id === (int) $user->id;
    }

    public function decide(User $user, RefundRequest $refundRequest): bool
    {
        return false;
    }
}
