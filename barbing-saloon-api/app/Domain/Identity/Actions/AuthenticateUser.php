<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Services\ApiTokenIssuer;
use App\Domain\Shared\Traits\HasAuditLog;
use App\Models\User;
use App\Support\Errors\AuthException;
use Illuminate\Support\Facades\Hash;

class AuthenticateUser
{
    use HasAuditLog;

    public function __construct(
        protected ApiTokenIssuer $tokenIssuer
    ) {}

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
            throw AuthException::invalidCredentials(
                'Invalid credentials. Please check your username, email, or password.'
            );
        }

        if (! $user->is_active || in_array($user->status, ['deactivated', 'suspended'], true)) {
            throw AuthException::accountSuspended(
                'Your account is currently inactive or suspended. Please contact support.'
            );
        }

        $client = request()->header('X-Client')
            ?? (request()->header('X-Client-Type') === 'mobile' || request()->hasHeader('X-App-Version') ? 'mobile' : 'web');
        $deviceLabel = $data['device_name'] ?? ($data['device_id'] ?? ($client === 'mobile' ? 'mobile-device' : 'browser'));
        $tokenName = "{$client}:{$deviceLabel}";

        $clientType = str_contains((string) $client, 'mobile') ? 'mobile' : 'web';
        $token = $this->tokenIssuer->issue($user, $tokenName, $clientType)->plainTextToken;
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
