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
        $token = $user->currentAccessToken();
        if ($token) {
            $token->delete();
        }
        $this->logAction('auth.logout', $user, [], ['email' => $user->email]);
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
        $this->logAction('auth.logout_all', $user, [], ['email' => $user->email]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listSessions(User $user): array
    {
        $currentTokenId = $user->currentAccessToken()?->id;

        return $user->tokens()
            ->orderByDesc('last_used_at')
            ->get()
            ->map(function ($token) use ($currentTokenId) {
                return [
                    'id' => $token->id,
                    'device_name' => $token->name,
                    'last_used_at' => $token->last_used_at?->toIso8601String(),
                    'created_at' => $token->created_at?->toIso8601String(),
                    'expires_at' => $token->expires_at?->toIso8601String(),
                    'is_current' => $token->id === $currentTokenId,
                ];
            })
            ->all();
    }

    public function revokeSession(User $user, int|string $tokenId): bool
    {
        $deleted = $user->tokens()->where('id', $tokenId)->delete();

        if ($deleted) {
            $this->logAction('auth.revoke_session', $user, [], [
                'token_id' => $tokenId,
                'email' => $user->email,
            ]);
        }

        return $deleted > 0;
    }
}
