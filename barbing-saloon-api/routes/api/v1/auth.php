<?php

use App\Http\Controllers\Api\V1\AuthApiController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/social-login', [AuthApiController::class, 'socialLogin']);
Route::post('/auth/login', [AuthApiController::class, 'login']);
Route::post('/auth/register', [AuthApiController::class, 'register']);
Route::post('/auth/forgot-password', [AuthApiController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthApiController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    Route::get('/auth/me', [AuthApiController::class, 'me']);
});
