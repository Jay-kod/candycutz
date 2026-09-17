<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Core\Enums\UserRole;
use App\Core\Traits\HasAuditLog;
use App\Models\User;
use App\Domain\Identity\Services\UsernameIdentityService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class AuthenticateUser
{
    use HasAuditLog;

    public function execute(array $data): array
    {
        $identifier = trim((string) ($data['identity'] ?? $data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        $cleanUsername = ltrim(strtolower($identifier), '@');

        $phoneAlternatives = [$identifier];
        if (str_starts_with($identifier, '+234')) {
            $phoneAlternatives[] = '0' . substr($identifier, 4);
        } elseif (str_starts_with($identifier, '0') && strlen($identifier) === 11) {
            $phoneAlternatives[] = '+234' . substr($identifier, 1);
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

        $tokenName = $data['device_name'] ?? 'web-client';
        $token = $user->createToken($tokenName)->plainTextToken;

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