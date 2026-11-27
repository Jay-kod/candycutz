<?php

use App\Http\Controllers\Api\V1\AppointmentApiController;
use App\Http\Controllers\Api\V1\AvailabilityApiController;
use App\Http\Controllers\Api\V1\NoShowApiController;
use Illuminate\Support\Facades\Route;

Route::get('/availability', [AvailabilityApiController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    // Draft Booking Lifecycle
    Route::get('/appointments/drafts', [AppointmentApiController::class, 'drafts']);
    Route::post('/appointments/draft', [AppointmentApiController::class, 'storeDraft']);
    Route::post('/appointments/drafts/{id}/resume', [AppointmentApiController::class, 'resumeDraft'])->whereNumber('id');
    Route::delete('/appointments/drafts/{id}', [AppointmentApiController::class, 'discardDraft'])->whereNumber('id');

    Route::get('/appointments', [AppointmentApiController::class, 'index']);
    Route::post('/appointments', [AppointmentApiController::class, 'store']);
    Route::middleware('check.role:barber,admin,super_admin')
        ->post('/appointments/walk-in', [AppointmentApiController::class, 'storeWalkIn']);
    Route::get('/appointments/{id}', [AppointmentApiController::class, 'show'])->whereNumber('id');
    Route::match(['post', 'patch'], '/appointments/{id}/cancel', [AppointmentApiController::class, 'cancel'])->whereNumber('id');
    Route::patch('/appointments/{id}/status', [AppointmentApiController::class, 'updateStatus'])->whereNumber('id');
    Route::patch('/appointments/{id}/approve', [AppointmentApiController::class, 'approve'])->whereNumber('id');
    Route::patch('/appointments/{id}/force-approve', [AppointmentApiController::class, 'forceApprove'])->whereNumber('id');

    // No-Show, Grace Period & Appeal Flows
    Route::middleware('check.role:barber,admin,super_admin')->group(function () {
        Route::patch('/appointments/{id}/no-show', [AppointmentApiController::class, 'noShow'])->whereNumber('id');
        Route::post('/appointments/{id}/forfeit-deposit', [NoShowApiController::class, 'forfeitDeposit'])->whereNumber('id');
    });
    Route::post('/appointments/{id}/grace-extension', [NoShowApiController::class, 'extendGrace'])->whereNumber('id');
    Route::post('/no-shows/{id}/appeal', [NoShowApiController::class, 'appeal'])->whereNumber('id');
});
