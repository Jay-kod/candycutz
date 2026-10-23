<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

class ApiTokenIssuer
{
    public function issue(User $user, string $name, string $client = 'web'): NewAccessToken
    {
        $role = $user->role instanceof \BackedEnum ? $user->role->value : $user->role;
        $isAdmin = in_array($role, ['admin', 'super_admin'], true);
        $normalizedClient = $client === 'mobile' ? 'mobile' : 'web';
        $abilities = ["client:{$normalizedClient}"];
        $expiresAt = $isAdmin ? now()->addHours(12) : now()->addDays(30);

        return $user->createToken($name, $abilities, $expiresAt);
    }
}
