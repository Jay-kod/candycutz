<?php

use App\Core\Http\Controllers\NotificationController;
use App\Modules\Customer\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer')->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard']);
    Route::get('/bookings', [CustomerController::class, 'bookings']);
    Route::get('/appointments', [CustomerController::class, 'bookings']);
    Route::post('/bookings', [CustomerController::class, 'storeBooking']);
    Route::get('/my-codes', [CustomerController::class, 'myCodes']);
    Route::get('/bookings/{appointment}', [CustomerController::class, 'showBooking']);
    Route::patch('/bookings/{appointment}/cancel', [CustomerController::class, 'cancelBooking']);
    Route::patch('/appointments/{appointment}/cancel', [CustomerController::class, 'cancelBooking']);
    Route::delete('/bookings/{appointment}', [CustomerController::class, 'deleteBooking']);

    // Wishlist
    Route::get('/wishlist', [CustomerController::class, 'wishlist']);
    Route::post('/wishlist', [CustomerController::class, 'addToWishlist']);
    Route::delete('/wishlist/item', [CustomerController::class, 'removeFromWishlistByType']);
    Route::delete('/wishlist/{id}', [CustomerController::class, 'removeFromWishlist']);

    // Checkout & Payments
    Route::post('/checkout', [CustomerController::class, 'checkout']);
    Route::get('/checkout/{id}/payment-details', [CustomerController::class, 'paymentDetails']);
    Route::post('/checkout/{id}/receipt', [CustomerController::class, 'uploadReceipt']);

    // Profile & Reviews
    Route::get('/profile', [CustomerController::class, 'profile']);
    Route::post('/profile', [CustomerController::class, 'updateProfile']);
    Route::get('/reviews', [CustomerController::class, 'reviews']);
    Route::post('/reviews', [CustomerController::class, 'storeReview']);
    Route::post('/testimonials', [CustomerController::class, 'storeReview']);

    // Blog Reactions
    Route::post('/blog/{id}/react', [CustomerController::class, 'reactToBlogPost']);
    Route::delete('/blog/{id}/react', [CustomerController::class, 'removeReactionFromBlogPost']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::get('/notification-settings', [NotificationController::class, 'getNotificationSettings']);
    Route::post('/notification-settings', [NotificationController::class, 'updateNotificationSettings']);

    // Analytics
    Route::get('/analytics', [CustomerController::class, 'analytics']);
});