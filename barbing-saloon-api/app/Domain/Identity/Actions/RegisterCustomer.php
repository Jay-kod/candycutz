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

class RegisterCustomer
{
    use HasAuditLog;

    public function __construct(
        protected UsernameIdentityService $usernameService
    ) {}

    public function execute(array $data): array
    {
        $name = trim($data['name']);
        
        if (!empty($data['username'])) {
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