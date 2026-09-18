<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Services\UsernameIdentityService;
use App\Domain\Shared\Enums\UserRole;
use App\Domain\Shared\Traits\HasAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class RegisterCustomer
{
    use HasAuditLog;

    public function __construct(
        protected UsernameIdentityService $usernameService
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{user: User, token: string}
     */
    public function execute(array $data): array
    {
        $name = trim($data['name']);

        if (! empty($data['username'])) {
            $username = strtolower(trim($data['username']));
            if (User::whereRaw('LOWER(username) = ?', [$username])->exists()) {
                throw new RuntimeException('This username is already taken. Please choose another.');
            }
        } else {
            $username = $this->usernameService->generateUniqueUsername($name);
        }

        $user = User::create([
            'name' => $name,
            'real_name' => $name,
            'username' => $username,
            'email' => strtolower(trim($data['email'])),
            'phone' => trim($data['phone']),
            'password' => Hash::make($data['password']),
            'role' => UserRole::customer,
            'is_active' => true,
            'status' => 'active',
            'last_username_change_at' => now(),
        ]);

        $tokenName = $data['device_name'] ?? 'web-client';
        $token = $user->createToken($tokenName)->plainTextToken;

        $this->logAction('auth.register', $user, [], [
            'email' => $user->email,
            'username' => $user->username,
            'role' => $user->role?->value ?? $user->role,
        ]);

        return [
            'user' => $user->loadMissing('barber'),
            'token' => $token,
        ];
    }
}
