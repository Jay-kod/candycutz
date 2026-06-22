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

    Route::get('/services', [BarberController::class, 'services']);
    Route::post('/services', [BarberController::class, 'storeService']);
    Route::put('/services/{service}', [BarberController::class, 'updateService']);
    Route::delete('/services/{service}', [BarberController::class, 'deleteService']);

    Route::get('/gallery', [BarberController::class, 'gallery']);
    Route::post('/gallery', [BarberController::class, 'storeGallery']);
    Route::delete('/gallery/{gallery}', [BarberController::class, 'deleteGallery']);

    Route::get('/blog', [BarberController::class, 'blogPosts']);
    Route::post('/blog', [BarberController::class, 'storeBlogPost']);
    Route::put('/blog/{blogPost}', [BarberController::class, 'updateBlogPost']);
    Route::delete('/blog/{blogPost}', [BarberController::class, 'deleteBlogPost']);
});