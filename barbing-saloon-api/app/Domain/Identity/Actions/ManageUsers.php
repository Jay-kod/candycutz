<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ManageUsers
{
    public function listUsers(): LengthAwarePaginator
    {
        return User::latest()->paginate(15);
    }

    public function storeUser(array $data): User
    {
        return User::create($data);
    }

    public function updateUser(User $user, array $data): User
    {
        $user->update($data);
        return $user->refresh();
    }

    public function activateUser(User $user): User
    {
        $user->update(['is_active' => true]);
        return $user->refresh();
    }

    public function deactivateUser(User $user): User
    {
        $user->update([
            'is_active' => false,
            'status' => 'deactivated',
            'deactivated_at' => now(),
        ]);
        $user->tokens()->delete();
        return $user->refresh();
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }
}