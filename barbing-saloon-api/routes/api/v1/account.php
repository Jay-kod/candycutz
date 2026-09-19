<?php

use App\Http\Controllers\Api\V1\AccountApiController;
use App\Http\Controllers\Api\V1\BarberApiController;
use App\Http\Controllers\Api\V1\WishlistApiController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Customer Account
    Route::get('/account/dashboard', [AccountApiController::class, 'dashboard']);
    Route::get('/account/profile', [AccountApiController::class, 'profile']);
    Route::post('/account/profile', [AccountApiController::class, 'updateProfile']);
    Route::get('/account/analytics', [AccountApiController::class, 'analytics']);
    Route::get('/account/my-codes', [AccountApiController::class, 'myCodes']);

    // Barber Account (using BarberApiController since it's barber specific logic, but could move to account later if consolidated)
    Route::get('/barbers/dashboard', [BarberApiController::class, 'dashboard']);
    Route::get('/barbers/analytics', [BarberApiController::class, 'analytics']);
    Route::get('/barbers/account', [BarberApiController::class, 'account']);
    Route::post('/barbers/account', [BarberApiController::class, 'updateAccount']);
    Route::put('/barbers/account', [BarberApiController::class, 'updateAccount']);
    Route::patch('/barbers/account/username', [BarberApiController::class, 'updateUsername']);
    Route::get('/barbers/schedule', [BarberApiController::class, 'schedule']);
    Route::put('/barbers/schedule', [BarberApiController::class, 'updateSchedule']);
    Route::get('/barbers/blocked-periods', [BarberApiController::class, 'blockedPeriods']);
    Route::post('/barbers/blocked-periods', [BarberApiController::class, 'storeBlockedPeriod']);
    Route::delete('/barbers/blocked-periods/{id}', [BarberApiController::class, 'deleteBlockedPeriod'])->whereNumber('id');

    // Wishlist
    Route::get('/wishlist', [WishlistApiController::class, 'index']);
    Route::post('/wishlist', [WishlistApiController::class, 'store']);
    Route::delete('/wishlist/remove', [WishlistApiController::class, 'destroyByType']);
    Route::delete('/wishlist/{id}', [WishlistApiController::class, 'destroy'])->whereNumber('id');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->whereNumber('id');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->whereNumber('id');
    Route::get('/notification-settings', [NotificationController::class, 'getNotificationSettings']);
    Route::post('/notification-settings', [NotificationController::class, 'updateNotificationSettings']);
    Route::post('/notifications/device-token', [NotificationController::class, 'registerDeviceToken']);
    Route::delete('/notifications/device-token', [NotificationController::class, 'deleteDeviceToken']);
});
