<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Events\PasswordChanged;
use App\Events\UserSuspended;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class ManageUsers
{
    /**
     * @return LengthAwarePaginator<User>
     */
    public function listUsers(): LengthAwarePaginator
    {
        Gate::authorize('manage-users');

        return User::latest()->paginate(15);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function storeUser(array $data): User
    {
        Gate::authorize('manage-users');

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
        Gate::authorize('manage-users');

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
        Gate::authorize('manage-users');

        $user->is_active = true;
        $user->status = 'active';
        $user->save();

        return $user->refresh();
    }

    public function deactivateUser(User $user): User
    {
        Gate::authorize('manage-users');

        $user->is_active = false;
        $user->status = 'deactivated';
        $user->deactivated_at = now();
        $user->save();

        $user->tokens()->delete();
        UserSuspended::dispatch($user, auth()->user(), 'Admin deactivated user account');

        return $user->refresh();
    }

    public function adminResetPassword(User $user, string $newPassword): User
    {
        Gate::authorize('manage-users');

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Invalidate existing tokens so they are forced to log in again
        $user->tokens()->delete();
        PasswordChanged::dispatch($user);

        return $user->refresh();
    }

    public function deleteUser(User $user): void
    {
        Gate::authorize('manage-users');

        $user->delete();
    }
}
