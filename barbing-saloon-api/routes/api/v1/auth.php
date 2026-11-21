<?php

use App\Http\Controllers\Api\V1\AuthApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:auth-sensitive')->group(function () {
    Route::post('/auth/login', [AuthApiController::class, 'login']);
    Route::post('/auth/register', [AuthApiController::class, 'register']);
    Route::post('/auth/forgot-password', [AuthApiController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthApiController::class, 'resetPassword']);
});
Route::post('/auth/social-login', [AuthApiController::class, 'socialLogin']);
Route::get('/auth/email/verify/{id}/{hash}', [AuthApiController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    Route::post('/auth/logout-all', [AuthApiController::class, 'logoutAll']);
    Route::post('/auth/change-password', [AuthApiController::class, 'changePassword']);
    Route::post('/auth/email/verification-notification', [AuthApiController::class, 'sendVerificationNotification'])
        ->middleware('throttle:auth-sensitive');
    Route::get('/auth/sessions', [AuthApiController::class, 'sessions']);
    Route::delete('/auth/sessions/{id}', [AuthApiController::class, 'revokeSession']);
    Route::get('/auth/me', [AuthApiController::class, 'me']);
});
