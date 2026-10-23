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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        $action->execute((string) $request->input('email'));

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

    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);
        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return ApiResponse::error('Invalid email verification link.', [], 403, 'EMAIL_VERIFICATION_INVALID');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return ApiResponse::success(null, 'Email address verified successfully.');
    }

    public function sendVerificationNotification(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(null, 'Email address is already verified.');
        }

        $user->sendEmailVerificationNotification();

        return ApiResponse::success(null, 'Email verification link sent.');
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

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (($user->role?->value ?? $user->role) === UserRole::barber->value) {
            $user->loadMissing('barber');
        }

        return ApiResponse::success(new AuthUserResource($user), 'Authenticated user');
    }
}
