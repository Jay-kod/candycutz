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

class ChangePassword
{
    use HasAuditLog;

    public function execute(User $user, array $data): bool
    {
        if (!Hash::check($data['current_password'], $user->password)) {
            throw new RuntimeException('Current password does not match our records.');
        }

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        $this->logAction('auth.change_password', $user, [], ['email' => $user->email]);

        return true;
    }
}