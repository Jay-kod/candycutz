<?php

use App\Core\Http\Controllers\NotificationController;
use App\Modules\Barber\Controllers\BarberController;
use Illuminate\Support\Facades\Route;

Route::prefix('barber')->group(function () {
    Route::get('/dashboard', [BarberController::class, 'dashboard']);
    Route::get('/schedule', [BarberController::class, 'schedule']);
    Route::put('/schedule', [BarberController::class, 'updateSchedule']);
    Route::get('/appointments', [BarberController::class, 'appointments']);
    Route::get('/bookings', [BarberController::class, 'bookings']);
    Route::patch('/appointments/{appointment}/complete', [BarberController::class, 'complete']);
    Route::patch('/appointments/{appointment}/no-show', [BarberController::class, 'noShow']);
    Route::patch('/appointments/{appointment}/approve', [BarberController::class, 'approveBooking']);
    Route::patch('/appointments/{appointment}/cancel', [BarberController::class, 'cancelBooking']);
    Route::post('/appointments/walk-in', [BarberController::class, 'createWalkIn']);
    Route::post('/bookings/{appointment}/verify-payment', [BarberController::class, 'verifyPayment']);

    Route::get('/profile', [BarberController::class, 'profile']);
    Route::post('/profile', [BarberController::class, 'updateProfile']);
    Route::patch('/my-status', [BarberController::class, 'updateMyStatus']);
    Route::get('/account', [BarberController::class, 'account']);
    Route::patch('/account', [BarberController::class, 'updateAccount']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Services
    Route::get('/services', [BarberController::class, 'services']);
    Route::post('/services', [BarberController::class, 'storeService']);
    Route::put('/services/{service}', [BarberController::class, 'updateService']);
    Route::delete('/services/{service}', [BarberController::class, 'deleteService']);

    // Gallery
    Route::get('/gallery', [BarberController::class, 'gallery']);
    Route::post('/gallery', [BarberController::class, 'storeGallery']);
    Route::post('/gallery/{gallery}', [BarberController::class, 'storeGallery']); // support update image with multipart
    Route::delete('/gallery/{gallery}', [BarberController::class, 'deleteGallery']);

    // Blog
    Route::get('/blog', [BarberController::class, 'blogPosts']);
    Route::post('/blog', [BarberController::class, 'storeBlogPost']);
    Route::put('/blog/{blogPost}', [BarberController::class, 'updateBlogPost']);
    Route::post('/blog/{blogPost}', [BarberController::class, 'updateBlogPost']); // for multipart update
    Route::delete('/blog/{blogPost}', [BarberController::class, 'deleteBlogPost']);

    // Analytics
    Route::get('/analytics', [BarberController::class, 'analytics']);
});