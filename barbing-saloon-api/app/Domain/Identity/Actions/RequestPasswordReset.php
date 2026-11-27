<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Traits\HasAuditLog;
use App\Models\User;
use App\Support\Errors\ErrorReporter;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class RequestPasswordReset
{
    use HasAuditLog;

    public function execute(string $email): bool
    {
        $user = User::whereRaw('LOWER(email) = ?', [strtolower(trim($email))])->first();

        if (! $user) {
            return true;
        }

        if (! $user->hasVerifiedEmail()) {
            throw new RuntimeException('Your email address has not been verified yet. Only accounts with verified emails can reset their password via email OTP. An administrator can override this and reset your password directly from the admin dashboard.');
        }

        // Generate 6-digit OTP code for password reset
        $otp = sprintf('%06d', random_int(100000, 999999));

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        Cache::put('password_reset_otp:'.strtolower($user->email), [
            'code' => $otp,
            'email' => $user->email,
        ], now()->addMinutes(15));

        try {
            $frontendUrl = (string) config('app.frontend_url', 'http://localhost:5173');
            $resetUrl = rtrim($frontendUrl, '/').'/reset-password?token='.$otp.'&code='.$otp.'&email='.urlencode($user->email);

            $viewHtml = view('mail.password-reset', [
                'userName' => $user->name,
                'otp' => $otp,
                'resetUrl' => $resetUrl,
            ])->render();

            Mail::send([], [], function ($message) use ($user, $viewHtml, $otp) {
                $message->to($user->email, $user->name)
                    ->subject("CandyCutz — Password Reset Code ({$otp})")
                    ->html($viewHtml);
            });
        } catch (Exception $e) {
            Log::error("Password reset email dispatch failed for {$user->email}: ".$e->getMessage(), [
                'recipient' => $user->email,
                'error' => $e->getMessage(),
            ]);

            try {
                app(ErrorReporter::class)->report($e);
            } catch (\Throwable) {
                // Ignore reporting failures
            }
        }

        $this->logAction('auth.forgot_password', $user, [], ['email' => $user->email]);

        return true;
    }
}
