<?php

use App\Http\Controllers\Api\V1\AdminApiController;
use App\Http\Controllers\Api\V1\GateApiController;
use App\Http\Controllers\Api\V1\SuperAdminApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'check.role:admin,super_admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminApiController::class, 'dashboard']);
    Route::get('/admin/gate/metrics', [GateApiController::class, 'metrics']);
    Route::get('/admin/gate/logs', [GateApiController::class, 'logs']);
    Route::post('/admin/gate/flush', [GateApiController::class, 'flush']);
    Route::get('/admin/settings', [AdminApiController::class, 'settings']);
    Route::post('/admin/settings', [AdminApiController::class, 'updateSettings']);
    Route::post('/admin/test-email', [AdminApiController::class, 'testEmail']);
    Route::get('/admin/logs', [AdminApiController::class, 'logs']);
    Route::get('/admin/verifications/stats', [AdminApiController::class, 'verificationStats']);
    Route::get('/admin/verifications', [AdminApiController::class, 'verifications']);
    Route::patch('/admin/verifications/{id}/verify', [AdminApiController::class, 'verifyAppointment']);
    Route::get('/admin/reports', [AdminApiController::class, 'reports']);
    Route::get('/admin/analytics', [AdminApiController::class, 'analytics']);
    Route::get('/admin/customers', [AdminApiController::class, 'customers']);
    Route::get('/admin/customers/{id}', [AdminApiController::class, 'customerProfile']);
    Route::get('/admin/working-hours', [AdminApiController::class, 'workingHours']);
    Route::put('/admin/working-hours/{barberId}', [AdminApiController::class, 'updateBarberWorkingHours']);
    Route::post('/admin/working-hours', [AdminApiController::class, 'storeWorkingHour']);
    Route::match(['put', 'patch'], '/admin/working-hours/{workingHour}', [AdminApiController::class, 'updateWorkingHour']);
    Route::delete('/admin/working-hours/{workingHour}', [AdminApiController::class, 'deleteWorkingHour']);
    Route::get('/admin/holidays', [AdminApiController::class, 'holidays']);
    Route::post('/admin/holidays', [AdminApiController::class, 'storeHoliday']);
    Route::match(['put', 'patch'], '/admin/holidays/{holiday}', [AdminApiController::class, 'updateHoliday']);
    Route::delete('/admin/holidays/{holiday}', [AdminApiController::class, 'deleteHoliday']);
});

Route::middleware(['auth:sanctum', 'check.role:super_admin'])->group(function () {
    Route::get('/super-admin/dashboard', [SuperAdminApiController::class, 'dashboard']);
    Route::get('/super-admin/users', [SuperAdminApiController::class, 'users']);
    Route::post('/super-admin/users', [SuperAdminApiController::class, 'storeUser']);
    Route::patch('/super-admin/users/{user}', [SuperAdminApiController::class, 'updateUser']);
    Route::patch('/super-admin/users/{user}/activate', [SuperAdminApiController::class, 'activateUser']);
    Route::patch('/super-admin/users/{user}/deactivate', [SuperAdminApiController::class, 'deactivateUser']);
    Route::delete('/super-admin/users/{user}', [SuperAdminApiController::class, 'deleteUser']);
    Route::get('/super-admin/settings', [SuperAdminApiController::class, 'settings']);
    Route::post('/super-admin/settings', [SuperAdminApiController::class, 'updateSettings']);
    Route::get('/super-admin/audit-logs', [SuperAdminApiController::class, 'auditLogs']);
});
