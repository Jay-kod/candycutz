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

class ResetPassword
{
    use HasAuditLog;

    public function execute(array $data): array
    {
        $email = strtolower(trim($data['email']));
        $rawToken = $data['token'];
        $newPassword = $data['password'];

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            throw new RuntimeException('Invalid or expired password reset token.');
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            throw new RuntimeException('This password reset link has expired. Please request a new one.');
        }

        if (!Hash::check($rawToken, $record->token)) {
            throw new RuntimeException('Invalid password reset token.');
        }

        $user = User::whereRaw('LOWER(email) = ?', [$email])->firstOrFail();

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $user->tokens()->delete();
        $freshToken = $user->createToken('web-client')->plainTextToken;

        $this->logAction('auth.reset_password', $user, [], ['email' => $user->email]);

        return [
            'user' => $user->loadMissing('barber'),
            'token' => $freshToken,
        ];
    }
}