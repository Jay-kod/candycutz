<?php

use App\Modules\SuperAdmin\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('super-admin')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard']);

    Route::get('/users', [SuperAdminController::class, 'users']);
    Route::post('/users', [SuperAdminController::class, 'storeUser']);
    Route::patch('/users/{user}', [SuperAdminController::class, 'updateUser']);
    Route::patch('/users/{user}/activate', [SuperAdminController::class, 'activateUser']);
    Route::patch('/users/{user}/deactivate', [SuperAdminController::class, 'deactivateUser']);
    Route::delete('/users/{user}', [SuperAdminController::class, 'deleteUser']);

    Route::get('/settings', [SuperAdminController::class, 'settings']);
    Route::post('/settings', [SuperAdminController::class, 'updateSettings']);

    Route::get('/audit-logs', [SuperAdminController::class, 'auditLogs']);
});