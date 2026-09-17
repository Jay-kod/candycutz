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

class ManageUserTokens
{
    use HasAuditLog;

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
        $this->logAction('auth.logout', $user, [], ['email' => $user->email]);
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
        $this->logAction('auth.logout_all', $user, [], ['email' => $user->email]);
    }
}