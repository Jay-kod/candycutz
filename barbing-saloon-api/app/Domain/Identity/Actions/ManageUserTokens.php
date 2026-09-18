<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Traits\HasAuditLog;
use App\Models\User;

class ManageUserTokens
{
    use HasAuditLog;

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
        $this->logAction('auth.logout', $user, [], ['email' => $user->email]);
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
        $this->logAction('auth.logout_all', $user, [], ['email' => $user->email]);
    }
}
