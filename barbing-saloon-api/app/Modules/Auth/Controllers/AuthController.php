<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Core\Http\Response\ApiResponse;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Resources\AuthUserResource;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AuthController
{
    public function __construct(protected AuthService $authService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $payload = $this->authService->register($request->validated());

            return ApiResponse::success([
                'user' => new AuthUserResource($payload['user']),
                'token' => $payload['token'],
            ], 'Registration successful', 201);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $payload = $this->authService->login($request->validated());
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), [], 401);
        }

        $barberProfile = null;
        if ($payload['user']->barber) {
            $b = $payload['user']->barber;
            $barberProfile = [
                'id' => $b->id,
                'user_id' => $payload['user']->id,
                'name' => $payload['user']->name,
                'real_name' => $payload['user']->real_name ?? $payload['user']->name,
                'username' => $payload['user']->username ?? 'barber',
                'email' => $payload['user']->email,
                'phone' => $payload['user']->phone,
                'avatar' => $payload['user']->avatar,
                'chair_status' => $b->chair_status ?? 'free',
                'is_active' => (bool) $payload['user']->is_active,
                'rating' => (float) ($b->rating ?? 5.0),
            ];
        }

        return ApiResponse::success([
            'user' => new AuthUserResource($payload['user']),
            'token' => $payload['token'],
            'barber' => $barberProfile,
        ], 'Login successful');
    }

    public function socialLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:google,apple'],
            'id_token' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:50'],
            'user_data' => ['nullable', 'array'],
        ]);

        try {
            $payload = $this->authService->socialLogin($validated);

            return ApiResponse::success([
                'user' => new AuthUserResource($payload['user']),
                'token' => $payload['token'],
            ], 'Social login successful');
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $this->authService->forgotPassword((string) $request->input('email'));

        return ApiResponse::success(
            null,
            'If an account exists for this email, a password reset link has been sent.'
        );
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $payload = $this->authService->resetPassword($validated);

            return ApiResponse::success([
                'user' => new AuthUserResource($payload['user']),
                'token' => $payload['token'],
            ], 'Password reset successful. You are now logged in.');
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $this->authService->changePassword($request->user(), $validated);

            return ApiResponse::success(null, 'Password updated successfully');
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(null, 'Logout successful');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());

        return ApiResponse::success(null, 'Logged out from all devices successfully');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->authService->me($request->user());

        return ApiResponse::success(new AuthUserResource($user), 'Authenticated user');
    }
}