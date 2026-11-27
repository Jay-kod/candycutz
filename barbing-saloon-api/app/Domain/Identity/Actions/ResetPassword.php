<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Services\ApiTokenIssuer;
use App\Domain\Shared\Traits\HasAuditLog;
use App\Events\PasswordChanged;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ResetPassword
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
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $rawToken = trim((string) ($data['token'] ?? $data['code'] ?? ''));
        $newPassword = (string) $data['password'];

        if ($email === '' || $rawToken === '') {
            throw new RuntimeException('Email and reset code/token are required.');
        }

        $attemptsKey = "password_reset_attempts:{$email}";
        $attempts = (int) Cache::get($attemptsKey, 0);

        if ($attempts >= 5) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            Cache::forget("password_reset_otp:{$email}");
            Cache::forget($attemptsKey);
            throw new RuntimeException('Too many invalid attempts. For security, this reset code has been burned. Please request a new code.');
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (! $record) {
            throw new RuntimeException('Invalid or expired password reset token.');
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            Cache::forget("password_reset_otp:{$email}");
            Cache::forget($attemptsKey);
            throw new RuntimeException('This password reset link has expired. Please request a new one.');
        }

        return DB::transaction(function () use ($email, $rawToken, $newPassword, $attemptsKey, $attempts) {
            $lockedRecord = DB::table('password_reset_tokens')
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if (! $lockedRecord) {
                throw new RuntimeException('Invalid or expired password reset token.');
            }

            $matches = Hash::check($rawToken, $lockedRecord->token);
            if (! $matches) {
                $cached = Cache::get("password_reset_otp:{$email}");
                $cachedCode = is_array($cached) ? ($cached['code'] ?? null) : $cached;
                if ($cachedCode && hash_equals((string) $cachedCode, $rawToken)) {
                    $matches = true;
                }
            }

            if (! $matches) {
                Cache::put($attemptsKey, $attempts + 1, now()->addMinutes(15));
                throw new RuntimeException('Invalid password reset token or OTP code.');
            }

            $user = User::whereRaw('LOWER(email) = ?', [$email])->lockForUpdate()->firstOrFail();

            $user->update([
                'password' => Hash::make($newPassword),
            ]);

            DB::table('password_reset_tokens')->where('email', $email)->delete();
            Cache::forget("password_reset_otp:{$email}");
            Cache::forget($attemptsKey);

            $user->tokens()->delete();
            $freshToken = $this->tokenIssuer->issue($user, 'web-client')->plainTextToken;

            $this->logAction('auth.reset_password', $user, [], ['email' => $user->email]);

            PasswordChanged::dispatch($user);

            return [
                'user' => $user->loadMissing('barber'),
                'token' => $freshToken,
            ];
        });
    }
}
