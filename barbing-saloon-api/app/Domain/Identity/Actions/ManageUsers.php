<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ManageUsers
{
    /**
     * @return LengthAwarePaginator<User>
     */
    public function listUsers(): LengthAwarePaginator
    {
        return User::latest()->paginate(15);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function storeUser(array $data): User
    {
        // Extract privilege fields before mass-assigning
        $role = $data['role'] ?? 'customer';
        $isActive = $data['is_active'] ?? true;
        $status = $data['status'] ?? 'active';
        unset($data['role'], $data['is_active'], $data['status']);

        $user = new User($data);
        $user->role = $role;
        $user->is_active = $isActive;
        $user->status = $status;
        $user->save();

        return $user;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateUser(User $user, array $data): User
    {
        // Extract and apply privilege fields explicitly
        if (array_key_exists('role', $data)) {
            $user->role = $data['role'];
            unset($data['role']);
        }
        if (array_key_exists('is_active', $data)) {
            $user->is_active = $data['is_active'];
            unset($data['is_active']);
        }
        if (array_key_exists('status', $data)) {
            $user->status = $data['status'];
            unset($data['status']);
        }

        $user->fill($data);
        $user->save();

        return $user->refresh();
    }

    public function activateUser(User $user): User
    {
        $user->is_active = true;
        $user->status = 'active';
        $user->save();

        return $user->refresh();
    }

    public function deactivateUser(User $user): User
    {
        $user->is_active = false;
        $user->status = 'deactivated';
        $user->deactivated_at = now();
        $user->save();

        $user->tokens()->delete();

        return $user->refresh();
    }

    public function adminResetPassword(User $user, string $newPassword): User
    {
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($newPassword),
        ]);
        
        // Invalidate existing tokens so they are forced to log in again
        $user->tokens()->delete();
        
        return $user->refresh();
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }
}
