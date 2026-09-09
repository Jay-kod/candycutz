<?php

declare(strict_types=1);

use App\Core\Http\Controllers\Api\V1\AppointmentApiController;
use App\Core\Http\Controllers\Api\V1\AvailabilityApiController;
use App\Core\Http\Controllers\Api\V1\BarberApiController;
use App\Core\Http\Controllers\Api\V1\HealthApiController;
use App\Core\Http\Controllers\Api\V1\PaymentWebhookApiController;
use App\Core\Http\Controllers\Api\V1\ServiceApiController;
use App\Core\Http\Controllers\Api\V1\ServiceZoneApiController;
use App\Core\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Public Canonical Endpoints
Route::get('/health', [HealthApiController::class, 'health']);
Route::get('/services', [ServiceApiController::class, 'index']);
Route::get('/service-categories', [ServiceApiController::class, 'categories']);
Route::get('/services/{idOrSlug}', [ServiceApiController::class, 'show']);
Route::get('/barbers', [BarberApiController::class, 'index']);
Route::get('/availability', [AvailabilityApiController::class, 'index']);
Route::get('/service-zones', [ServiceZoneApiController::class, 'index']);
Route::post('/payments/webhook', [PaymentWebhookApiController::class, 'handle']);

// Authenticated Canonical Endpoints
Route::middleware('auth:sanctum')->group(function () {
    // Specific Barber Operations (declared before /barbers/{id} wildcard)
    Route::patch('/barbers/chair-status', [BarberApiController::class, 'updateChairStatus']);
    Route::get('/barbers/my-appointments', [AppointmentApiController::class, 'index']);
    Route::get('/barbers/schedule', [BarberApiController::class, 'schedule']);
    Route::put('/barbers/schedule', [BarberApiController::class, 'updateSchedule']);
    Route::get('/barbers/blocked-periods', [BarberApiController::class, 'blockedPeriods']);
    Route::post('/barbers/blocked-periods', [BarberApiController::class, 'storeBlockedPeriod']);
    Route::delete('/barbers/blocked-periods/{id}', [BarberApiController::class, 'deleteBlockedPeriod'])->whereNumber('id');
    Route::post('/barbers/walk-in', [AppointmentApiController::class, 'storeWalkIn']);

    // Appointments Lifecycle (static routes before {id} wildcard)
    Route::get('/appointments', [AppointmentApiController::class, 'index']);
    Route::post('/appointments', [AppointmentApiController::class, 'store']);
    Route::post('/appointments/walk-in', [AppointmentApiController::class, 'storeWalkIn']);
    Route::get('/appointments/{id}', [AppointmentApiController::class, 'show'])->whereNumber('id');
    Route::match(['post', 'patch'], '/appointments/{id}/cancel', [AppointmentApiController::class, 'cancel'])->whereNumber('id');
    Route::patch('/appointments/{id}/status', [AppointmentApiController::class, 'updateStatus'])->whereNumber('id');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->whereNumber('id');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->whereNumber('id');
    Route::get('/notification-settings', [NotificationController::class, 'getNotificationSettings']);
    Route::post('/notification-settings', [NotificationController::class, 'updateNotificationSettings']);
});

// Single barber lookup (after specific barber routes, constrained to numeric ID)
Route::get('/barbers/{id}', [BarberApiController::class, 'show'])->whereNumber('id');
