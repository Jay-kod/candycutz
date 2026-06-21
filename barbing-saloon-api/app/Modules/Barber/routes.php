<?php

use App\Modules\Barber\Controllers\BarberController;
use Illuminate\Support\Facades\Route;

Route::prefix('barber')->group(function () {
    Route::get('/dashboard', [BarberController::class, 'dashboard']);
    Route::get('/schedule', [BarberController::class, 'schedule']);
    Route::get('/appointments', [BarberController::class, 'appointments']);
    Route::patch('/appointments/{appointment}/complete', [BarberController::class, 'complete']);
    Route::patch('/appointments/{appointment}/no-show', [BarberController::class, 'noShow']);
    Route::get('/profile', [BarberController::class, 'profile']);
    Route::post('/profile', [BarberController::class, 'updateProfile']);
});