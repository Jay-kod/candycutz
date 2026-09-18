<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Traits\HasAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ChangePassword
{
    use HasAuditLog;

    public function execute(User $user, array $data): bool
    {
        if (! Hash::check($data['current_password'], $user->password)) {
            throw new RuntimeException('Current password does not match our records.');
        }

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        $this->logAction('auth.change_password', $user, [], ['email' => $user->email]);

        return true;
    }
}
