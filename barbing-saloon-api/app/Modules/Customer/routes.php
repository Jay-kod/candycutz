<?php

use App\Modules\Customer\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer')->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard']);
    Route::get('/bookings', [CustomerController::class, 'bookings']);
    Route::post('/bookings', [CustomerController::class, 'storeBooking']);
    Route::get('/bookings/{appointment}', [CustomerController::class, 'showBooking']);
    Route::patch('/bookings/{appointment}/cancel', [CustomerController::class, 'cancelBooking']);
    Route::get('/profile', [CustomerController::class, 'profile']);
    Route::post('/profile', [CustomerController::class, 'updateProfile']);
    Route::get('/reviews', [CustomerController::class, 'reviews']);
    Route::post('/reviews', [CustomerController::class, 'storeReview']);
});