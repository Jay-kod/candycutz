<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Events\PasswordChanged;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SetUserPassword
{
    /**
     * Replace the user's password hash, clear any remember token,
     * revoke active access tokens, and emit PasswordChanged.
     */
    public function execute(User $user, string $newPassword, bool $revokeTokens = true): void
    {
        $user->forceFill([
            'password' => Hash::make($newPassword),
            'remember_token' => null,
        ])->save();

        if ($revokeTokens) {
            $user->tokens()->delete();
        }

        PasswordChanged::dispatch($user);
    }
}
