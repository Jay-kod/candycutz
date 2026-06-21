<?php

namespace App\Modules\Auth\Services;

use App\Core\Traits\HasAuditLog;
use App\Core\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AuthService
{
    use HasAuditLog;

    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => UserRole::customer,
            'is_active' => true,
        ]);

        $this->logAction('auth.register', $user, [], ['email' => $user->email, 'role' => $user->role?->value ?? $user->role]);

        return $user;
    }

    public function login(array $data): array
    {
        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            $this->logFailure($data['email']);

            throw new RuntimeException('Invalid credentials');
        }

        /** @var User $user */
        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        $this->logAction('auth.login', $user, [], ['email' => $user->email]);

        return [
            'user' => $user->loadMissing('barber'),
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
        $this->logAction('auth.logout', $user, [], ['email' => $user->email]);
    }

    public function me(User $user): User
    {
        if (($user->role?->value ?? $user->role) === UserRole::barber->value) {
            return $user->loadMissing('barber');
        }

        return $user;
    }

    protected function logFailure(string $email): void
    {
        $ghost = new User(['email' => $email, 'name' => 'Guest']);

        $this->logAction('auth.login_failed', $ghost, [], ['email' => $email]);
    }
}