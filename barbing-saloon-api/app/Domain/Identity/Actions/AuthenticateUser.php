<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Enums\UserRole;
use App\Domain\Shared\Traits\HasAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AuthenticateUser
{
    use HasAuditLog;

    /**
     * @param  array<string, mixed>  $data
     * @return array{user: User, token: string}
     */
    public function execute(array $data): array
    {
        $identifier = trim((string) ($data['identity'] ?? $data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        $cleanUsername = ltrim(strtolower($identifier), '@');

        $phoneAlternatives = [$identifier];
        if (str_starts_with($identifier, '+234')) {
            $phoneAlternatives[] = '0'.substr($identifier, 4);
        } elseif (str_starts_with($identifier, '0') && strlen($identifier) === 11) {
            $phoneAlternatives[] = '+234'.substr($identifier, 1);
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($identifier)])
            ->orWhereRaw('LOWER(username) = ?', [$cleanUsername])
            ->orWhereIn('phone', $phoneAlternatives)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            $this->logFailure($identifier);
            throw new RuntimeException('Invalid credentials. Please check your username, email, or password.');
        }

        if (! $user->is_active || in_array($user->status, ['deactivated', 'suspended'], true)) {
            throw new RuntimeException('Your account is currently inactive or suspended. Please contact support.');
        }

        $clientType = request()?->header('X-Client-Type');
        $defaultDeviceName = $clientType === 'mobile' ? 'mobile-app' : 'web-client';
        $tokenName = $data['device_name'] ?? $defaultDeviceName;
        $expiresAt = $user->role === UserRole::customer
            ? now()->addDays(30)
            : now()->addHours(12);

        $token = $user->createToken($tokenName, ['*'], $expiresAt)->plainTextToken;
        $this->logAction('auth.login', $user, [], [
            'email' => $user->email,
            'username' => $user->username,
            'device' => $tokenName,
        ]);

        return [
            'user' => $user->loadMissing('barber'),
            'token' => $token,
        ];
    }

    protected function logFailure(string $identifier): void
    {
        $ghost = new User(['email' => $identifier, 'name' => 'Guest']);
        $this->logAction('auth.login_failed', $ghost, [], ['identifier' => $identifier]);
    }
}
