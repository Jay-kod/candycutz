<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Carbon\Carbon;
use Exception;

class UsernameIdentityService
{
    protected array $reservedUsernames = [
        'admin',
        'superadmin',
        'candycutz',
        'support',
        'root',
        'barber',
        'keffi',
        'billing',
        'help',
        'manager',
        'owner',
        'official',
        'staff',
    ];

    /**
     * Validate and assign/update a user's @username.
     */
    public function updateUsername(User $user, string $newUsername, bool $isSuperAdminOverride = false): User
    {
        $normalized = strtolower(trim($newUsername));

        // 1. Format check: 3-30 chars, letters, numbers, underscore, hyphen
        if (!preg_match('/^[a-z0-9_-]{3,30}$/', $normalized)) {
            throw new Exception('Username must be 3-30 characters and contain only letters, numbers, underscores, and hyphens.');
        }

        // 2. Reserved handle check
        if (in_array($normalized, $this->reservedUsernames, true)) {
            throw new Exception('This username is reserved by the CandyCutz system.');
        }

        // 3. Uniqueness check (excluding current user)
        $exists = User::whereRaw('LOWER(username) = ?', [$normalized])
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            throw new Exception('This username is already taken. Please choose another.');
        }

        // 4. 90-Day Cooldown enforcement (unless bypassed by Super Admin)
        if (!$isSuperAdminOverride && $user->last_username_change_at) {
            $daysSinceChange = Carbon::parse($user->last_username_change_at)->diffInDays(now());
            if ($daysSinceChange < 90) {
                $daysRemaining = 90 - $daysSinceChange;
                throw new Exception("Username can only be changed once every 90 days. {$daysRemaining} days remaining.");
            }
        }

        // 5. Apply update
        $user->update([
            'username' => $normalized,
            'status' => ($user->status === 'username_pending') ? 'active' : $user->status,
            'last_username_change_at' => now(),
        ]);

        return $user;
    }
}
