<?php

use App\Modules\Landing\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    Route::get('/settings', [PublicController::class, 'settings']);
    Route::get('/services', [PublicController::class, 'services']);
    Route::get('/services/{slug}', [PublicController::class, 'service']);
    Route::get('/service-categories', [PublicController::class, 'serviceCategories']);
    Route::get('/barbers', [PublicController::class, 'barbers']);
    Route::get('/barbers/{id}', [PublicController::class, 'barber']);
    Route::get('/gallery', [PublicController::class, 'gallery']);
    Route::get('/testimonials', [PublicController::class, 'testimonials']);
    Route::get('/blog', [PublicController::class, 'blog']);
    Route::get('/blog/{slug}', [PublicController::class, 'blogPost']);
    Route::get('/working-hours', [PublicController::class, 'workingHours']);
    Route::get('/available-slots', [PublicController::class, 'availableSlots']);
    Route::post('/contact', [PublicController::class, 'contact']);
});