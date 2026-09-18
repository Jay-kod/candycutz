<?php

use App\Http\Controllers\Api\V1\AppointmentApiController;
use App\Http\Controllers\Api\V1\AvailabilityApiController;
use Illuminate\Support\Facades\Route;

Route::get('/availability', [AvailabilityApiController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/appointments', [AppointmentApiController::class, 'index']);
    Route::post('/appointments', [AppointmentApiController::class, 'store']);
    Route::post('/appointments/walk-in', [AppointmentApiController::class, 'storeWalkIn']);
    Route::get('/appointments/{id}', [AppointmentApiController::class, 'show'])->whereNumber('id');
    Route::match(['post', 'patch'], '/appointments/{id}/cancel', [AppointmentApiController::class, 'cancel'])->whereNumber('id');
    Route::patch('/appointments/{id}/status', [AppointmentApiController::class, 'updateStatus'])->whereNumber('id');
    Route::patch('/appointments/{id}/approve', [AppointmentApiController::class, 'approve'])->whereNumber('id');
    Route::patch('/appointments/{id}/force-approve', [AppointmentApiController::class, 'forceApprove'])->whereNumber('id');
});
