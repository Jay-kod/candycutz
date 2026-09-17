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
                $frontendUrl = config('app.frontend_url') ?: env('FRONTEND_URL', 'http://localhost:5173');
                $resetUrl = "{$frontendUrl}/reset-password?token={$token}&email=" . urlencode($user->email);

                Mail::send([], [], function ($message) use ($user, $resetUrl) {
                    $message->to($user->email, $user->name)
                        ->subject('CandyCutz — Password Reset Request')
                        ->html("
                            <div style='font-family: sans-serif; background: #0b0b0f; color: #f8fafc; padding: 40px;'>
                                <h2 style='color: #d4af37;'>CandyCutz Luxury Grooming</h2>
                                <p>Hello {$user->name},</p>
                                <p>We received a request to reset your CandyCutz account password.</p>
                                <p style='margin: 30px 0;'>
                                    <a href='{$resetUrl}' style='background: #d4af37; color: #0b0b0f; padding: 12px 24px; text-decoration: none; font-weight: bold; border-radius: 6px;'>
                                        Reset Password
                                    </a>
                                </p>
                                <p style='color: #94a3b8; font-size: 13px;'>If you did not request this, please ignore this email. This link will expire in 60 minutes.</p>
                            </div>
                        ");
                });
            } catch (Exception $e) {
                Log::warning("Password reset email dispatch failed for {$user->email}: " . $e->getMessage());
            }

            $this->logAction('auth.forgot_password', $user, [], ['email' => $user->email]);
        }

        return true;
    }
}