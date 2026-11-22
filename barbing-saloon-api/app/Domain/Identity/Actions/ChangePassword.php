<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Traits\HasAuditLog;
use App\Events\PasswordChanged;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ChangePassword
{
    use HasAuditLog;

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): bool
    {
        if (! Hash::check($data['current_password'], $user->password)) {
            throw new RuntimeException('Current password does not match our records.');
        }

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        // Terminate all other active device tokens immediately upon password change
        $token = $user->currentAccessToken();
        $currentTokenId = ($token instanceof \Laravel\Sanctum\PersonalAccessToken) ? $token->id : null;
        if ($currentTokenId) {
            $user->tokens()->where('id', '!=', $currentTokenId)->delete();
        } elseif (! ($token instanceof \Laravel\Sanctum\TransientToken)) {
            $user->tokens()->delete();
        }

        $this->logAction('auth.change_password', $user, [], ['email' => $user->email]);

        PasswordChanged::dispatch($user);

        return true;
    }
}
