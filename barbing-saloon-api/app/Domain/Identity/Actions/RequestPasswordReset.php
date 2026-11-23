<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Traits\HasAuditLog;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RequestPasswordReset
{
    use HasAuditLog;

    public function execute(string $email): bool
    {
        $user = User::whereRaw('LOWER(email) = ?', [strtolower(trim($email))])->first();

        if ($user) {
            $token = Str::random(60);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            try {
                $frontendUrl = config('app.frontend_url');
                $resetUrl = "{$frontendUrl}/reset-password?token={$token}&email=".urlencode($user->email);

                $viewHtml = view('mail.password-reset', [
                    'userName' => $user->name,
                    'resetUrl' => $resetUrl,
                ])->render();

                Mail::send([], [], function ($message) use ($user, $viewHtml) {
                    $message->to($user->email, $user->name)
                        ->subject('CandyCutz — Password Reset Request')
                        ->html($viewHtml);
                });
            } catch (Exception $e) {
                Log::error("Password reset email dispatch failed for {$user->email}: ".$e->getMessage(), [
                    'recipient' => $user->email,
                    'error' => $e->getMessage(),
                ]);

                try {
                    app(\App\Support\Errors\ErrorReporter::class)->report($e);
                } catch (\Throwable) {
                    // Ignore reporting failures
                }
            }

            $this->logAction('auth.forgot_password', $user, [], ['email' => $user->email]);
        }

        return true;
    }
}
