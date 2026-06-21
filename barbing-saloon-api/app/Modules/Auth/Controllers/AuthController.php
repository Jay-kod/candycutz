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

        return ApiResponse::success([
            'user' => new AuthUserResource($payload['user']),
            'token' => $payload['token'],
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