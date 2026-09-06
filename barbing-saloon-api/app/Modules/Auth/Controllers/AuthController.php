<?php

namespace App\Modules\Auth\Controllers;

use App\Core\Http\Response\ApiResponse;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Resources\AuthUserResource;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class AuthController
{
    public function __construct(protected AuthService $authService)
    {
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

        return ApiResponse::success(new AuthUserResource($user), 'Registration successful', 201);
    }

    public function login(LoginRequest $request)
    {
        try {
            $payload = $this->authService->login($request->validated());
        } catch (RuntimeException) {
            return ApiResponse::error('Invalid credentials', [], 401);
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

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(null, 'Logout successful');
    }

    public function me(Request $request)
    {
        $user = $this->authService->me($request->user());

        return ApiResponse::success(new AuthUserResource($user), 'Authenticated user');
    }
}