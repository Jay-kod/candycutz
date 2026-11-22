<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Identity\Actions\AuthenticateUser;
use App\Domain\Identity\Actions\ChangePassword;
use App\Domain\Identity\Actions\ManageUserTokens;
use App\Domain\Identity\Actions\RegisterCustomer;
use App\Domain\Identity\Actions\RequestPasswordReset;
use App\Domain\Identity\Actions\ResetPassword;
use App\Domain\Identity\Actions\SocialLogin;
use App\Domain\Shared\Enums\UserRole;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\AuthUserResource;
use App\Http\Resources\BarberResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

class AuthApiController
{
    public function register(RegisterRequest $request, RegisterCustomer $action): JsonResponse
    {
        try {
            $payload = $action->execute($request->validated());

            return ApiResponse::success([
                'user' => new AuthUserResource($payload['user']),
                'token' => $payload['token'],
            ], 'Registration successful', 201);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function login(LoginRequest $request, AuthenticateUser $action): JsonResponse
    {
        $payload = $action->execute($request->validated());

        return ApiResponse::success([
            'user' => new AuthUserResource($payload['user']),
            'token' => $payload['token'],
            'barber' => $payload['user']->barber ? new BarberResource($payload['user']->barber) : null,
        ], 'Login successful');
    }

    public function socialLogin(Request $request, SocialLogin $action): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:google,apple'],
            'id_token' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:50'],
            'user_data' => ['nullable', 'array'],
        ]);

        try {
            $payload = $action->execute($validated);

            return ApiResponse::success([
                'user' => new AuthUserResource($payload['user']),
                'token' => $payload['token'],
            ], 'Social login successful');
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), [], 400, 'SOCIAL_LOGIN_FAILED');
        } catch (\Throwable $e) {
            Log::error('Social login failed unexpectedly', [
                'provider' => $validated['provider'],
                'exception' => $e,
            ]);

            return ApiResponse::error(
                'Google sign-in could not be completed. Please try again.',
                [],
                422,
                'SOCIAL_LOGIN_FAILED'
            );
        }
    }

    public function forgotPassword(Request $request, RequestPasswordReset $action): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = (string) $request->input('email');
        $throttleKey = 'forgot-password:'.strtolower($email).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return ApiResponse::error(
                "Too many password reset attempts. Please try again in {$seconds} seconds.",
                ['retry_after' => $seconds],
                429,
                'TOO_MANY_REQUESTS'
            );
        }
        RateLimiter::hit($throttleKey, 60);

        $action->execute($email);

        return ApiResponse::success(
            null,
            'If an account exists for this email, a password reset link has been sent.'
        );
    }

    public function resetPassword(Request $request, ResetPassword $action): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $payload = $action->execute($validated);

            return ApiResponse::success([
                'user' => new AuthUserResource($payload['user']),
                'token' => $payload['token'],
            ], 'Password reset successful. You are now logged in.');
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse|RedirectResponse
    {
        $user = User::findOrFail($id);
        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            if ($request->wantsJson()) {
                return ApiResponse::error('Invalid email verification link.', [], 403, 'EMAIL_VERIFICATION_INVALID');
            }

            $frontendUrl = (string) config('app.frontend_url', 'http://localhost:5173');

            return redirect()->away(rtrim($frontendUrl, '/').'/verify-email?status=invalid');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        if ($request->wantsJson()) {
            return ApiResponse::success([
                'is_email_verified' => true,
                'email_verified_at' => $user->email_verified_at instanceof \Carbon\CarbonInterface
                    ? $user->email_verified_at->toIso8601String()
                    : ($user->email_verified_at ? (string) $user->email_verified_at : null),
            ], 'Email address verified successfully.');
        }

        $frontendUrl = (string) config('app.frontend_url', 'http://localhost:5173');

        return redirect()->away(rtrim($frontendUrl, '/').'/verify-email?status=success&email='.urlencode($user->email));
    }

    public function sendVerificationNotification(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success([
                'is_email_verified' => true,
                'email_verified_at' => $user->email_verified_at instanceof \Carbon\CarbonInterface
                    ? $user->email_verified_at->toIso8601String()
                    : ($user->email_verified_at ? (string) $user->email_verified_at : null),
            ], 'Email address is already verified.');
        }

        $throttleKey = "email-verification-notification:{$user->id}";
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return ApiResponse::error(
                "Please wait {$seconds} seconds before requesting another verification code.",
                ['retry_after' => $seconds],
                429,
                'TOO_MANY_REQUESTS'
            );
        }
        RateLimiter::hit($throttleKey, 60);

        // Pre-generate and store OTP in cache so it is immediately available
        $cacheKey = "email_verification_otp:{$user->id}";
        $otp = sprintf('%06d', random_int(100000, 999999));
        Cache::put($cacheKey, [
            'code' => $otp,
            'email' => $user->email,
        ], now()->addMinutes(15));

        $user->sendEmailVerificationNotification();

        return ApiResponse::success([
            'email' => $user->email,
            'is_email_verified' => false,
            'expires_in' => 900,
            'cooldown' => 60,
        ], 'Email verification link and 6-digit code sent successfully.');
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success([
                'user' => new AuthUserResource($user),
                'is_email_verified' => true,
                'email_verified_at' => $user->email_verified_at instanceof \Carbon\CarbonInterface
                    ? $user->email_verified_at->toIso8601String()
                    : ($user->email_verified_at ? (string) $user->email_verified_at : null),
            ], 'Email address is already verified.');
        }

        $cacheKey = "email_verification_otp:{$user->id}";
        $cachedData = Cache::get($cacheKey);

        $cachedCode = is_array($cachedData) ? ($cachedData['code'] ?? null) : $cachedData;
        $cachedEmail = is_array($cachedData) ? ($cachedData['email'] ?? null) : null;

        if (
            ! $cachedCode
            || ! hash_equals((string) $cachedCode, trim((string) $validated['code']))
            || ($cachedEmail !== null && strtolower((string) $cachedEmail) !== strtolower((string) $user->email))
        ) {
            return ApiResponse::error(
                'Invalid or expired verification code. Please check your code or request a new one.',
                [],
                422,
                'INVALID_VERIFICATION_CODE'
            );
        }

        $user->markEmailAsVerified();
        event(new Verified($user));
        Cache::forget($cacheKey);

        $fresh = $user->fresh();

        return ApiResponse::success([
            'user' => new AuthUserResource($fresh),
            'is_email_verified' => true,
            'email_verified_at' => $fresh->email_verified_at instanceof \Carbon\CarbonInterface
                ? $fresh->email_verified_at->toIso8601String()
                : ($fresh->email_verified_at ? (string) $fresh->email_verified_at : null),
        ], 'Email address verified successfully. Your account is secured and verified.');
    }

    public function emailStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        return ApiResponse::success([
            'email' => $user->email,
            'is_email_verified' => $user->hasVerifiedEmail(),
            'email_verified_at' => $user->email_verified_at instanceof \Carbon\CarbonInterface
                ? $user->email_verified_at->toIso8601String()
                : ($user->email_verified_at ? (string) $user->email_verified_at : null),
        ], 'Email verification status retrieved');
    }

    public function changePassword(Request $request, ChangePassword $action): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $action->execute($request->user(), $validated);

            return ApiResponse::success(null, 'Password updated successfully');
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function logout(Request $request, ManageUserTokens $action): JsonResponse
    {
        $action->logout($request->user());

        return ApiResponse::success(null, 'Logout successful');
    }

    public function logoutAll(Request $request, ManageUserTokens $action): JsonResponse
    {
        $action->logoutAll($request->user());

        return ApiResponse::success(null, 'Logged out from all devices successfully');
    }

    public function sessions(Request $request, ManageUserTokens $action): JsonResponse
    {
        $sessions = $action->listSessions($request->user());

        return ApiResponse::success($sessions, 'Active sessions retrieved successfully');
    }

    public function revokeSession(Request $request, int|string $id, ManageUserTokens $action): JsonResponse
    {
        $action->revokeSession($request->user(), $id);

        return ApiResponse::success(null, 'Session revoked successfully');
    }

    public function revokeOtherSessions(Request $request, ManageUserTokens $action): JsonResponse
    {
        $count = $action->revokeOtherSessions($request->user());

        return ApiResponse::success([
            'revoked_count' => $count,
        ], 'All other active sessions have been revoked successfully');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (($user->role->value ?? $user->role) === UserRole::barber->value) {
            $user->loadMissing('barber');
        }

        return ApiResponse::success(new AuthUserResource($user), 'Authenticated user');
    }
}
