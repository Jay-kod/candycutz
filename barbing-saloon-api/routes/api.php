<?php

use Illuminate\Support\Facades\Route;
use App\Core\Http\Controllers\NotificationController;

Route::get('/health', fn () => response()->json(['success' => true, 'message' => 'OK']));

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});