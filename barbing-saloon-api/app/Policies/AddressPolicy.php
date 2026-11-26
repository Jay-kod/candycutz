<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Address;
use App\Models\User;

class AddressPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        $role = $user->role instanceof \BackedEnum ? (string) $user->role->value : (string) $user->role;

        if (in_array($role, ['admin', 'super_admin'], true)) {
            return true;
        }

        return null;
    }

    public function view(User $user, Address $address): bool
    {
        return (int) $address->user_id === (int) $user->id;
    }

    public function update(User $user, Address $address): bool
    {
        return (int) $address->user_id === (int) $user->id;
    }

    public function delete(User $user, Address $address): bool
    {
        return (int) $address->user_id === (int) $user->id;
    }
}
