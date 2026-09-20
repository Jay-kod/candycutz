<?php

use App\Http\Controllers\Api\V1\AccountApiController;
use App\Http\Controllers\Api\V1\AppointmentApiController;
use App\Http\Controllers\Api\V1\BarberApiController;
use App\Http\Controllers\Api\V1\BlogApiController;
use App\Http\Controllers\Api\V1\GalleryApiController;
use App\Http\Controllers\Api\V1\PaymentApiController;
use App\Http\Controllers\Api\V1\ServiceApiController;
use App\Http\Controllers\Api\V1\TestimonialApiController;
use App\Http\Controllers\Api\V1\WishlistApiController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // -------------------------------------------------------------------------
    // Customer Portal Routes (/api/v1/customer/* and /api/v1/account/*)
    // -------------------------------------------------------------------------
    Route::get('/account/dashboard', [AccountApiController::class, 'dashboard']);
    Route::get('/customer/dashboard', [AccountApiController::class, 'dashboard']);

    Route::get('/account/profile', [AccountApiController::class, 'profile']);
    Route::post('/account/profile', [AccountApiController::class, 'updateProfile']);
    Route::get('/customer/profile', [AccountApiController::class, 'profile']);
    Route::post('/customer/profile', [AccountApiController::class, 'updateProfile']);

    Route::get('/account/analytics', [AccountApiController::class, 'analytics']);
    Route::get('/customer/analytics', [AccountApiController::class, 'analytics']);

    Route::get('/account/my-codes', [AccountApiController::class, 'myCodes']);
    Route::get('/customer/my-codes', [AccountApiController::class, 'myCodes']);

    // Customer Bookings
    Route::get('/customer/bookings', [AppointmentApiController::class, 'index']);
    Route::post('/customer/bookings', [AppointmentApiController::class, 'store']);
    Route::get('/customer/appointments', [AppointmentApiController::class, 'index']);
    Route::get('/customer/bookings/{id}', [AppointmentApiController::class, 'show'])->whereNumber('id');
    Route::patch('/customer/bookings/{id}/cancel', [AppointmentApiController::class, 'cancel'])->whereNumber('id');
    Route::patch('/customer/appointments/{id}/cancel', [AppointmentApiController::class, 'cancel'])->whereNumber('id');
    Route::delete('/customer/bookings/{id}', [AppointmentApiController::class, 'cancel'])->whereNumber('id');

    // Customer Reviews / Testimonials
    Route::get('/customer/reviews', [TestimonialApiController::class, 'customerReviews']);
    Route::post('/customer/testimonials', [TestimonialApiController::class, 'store']);

    // Customer Wishlist
    Route::get('/wishlist', [WishlistApiController::class, 'index']);
    Route::post('/wishlist', [WishlistApiController::class, 'store']);
    Route::delete('/wishlist/remove', [WishlistApiController::class, 'destroyByType']);
    Route::delete('/wishlist/{id}', [WishlistApiController::class, 'destroy'])->whereNumber('id');
    Route::get('/customer/wishlist', [WishlistApiController::class, 'index']);
    Route::post('/customer/wishlist', [WishlistApiController::class, 'store']);
    Route::delete('/customer/wishlist/item', [WishlistApiController::class, 'destroyByType']);
    Route::delete('/customer/wishlist/{id}', [WishlistApiController::class, 'destroy'])->whereNumber('id');

    // Customer Checkout & Receipts
    Route::post('/customer/checkout', [PaymentApiController::class, 'checkout']);
    Route::get('/customer/checkout/{id}/payment-details', [PaymentApiController::class, 'paymentDetails'])->whereNumber('id');
    Route::post('/customer/checkout/{id}/receipt', [PaymentApiController::class, 'uploadReceipt'])->whereNumber('id');

    // Customer Blog Interactions
    Route::post('/customer/blog/{id}/react', [BlogApiController::class, 'react'])->whereNumber('id');
    Route::delete('/customer/blog/{id}/react', [BlogApiController::class, 'removeReaction'])->whereNumber('id');

    // Customer Notifications
    Route::get('/customer/notifications', [NotificationController::class, 'index']);
    Route::post('/customer/notification-settings', [NotificationController::class, 'updateNotificationSettings']);

    // -------------------------------------------------------------------------
    // Barber Portal Routes (/api/v1/barber/* and /api/v1/barbers/*)
    // -------------------------------------------------------------------------
    Route::get('/barbers/dashboard', [BarberApiController::class, 'dashboard']);
    Route::get('/barber/dashboard', [BarberApiController::class, 'dashboard']);

    Route::get('/barbers/analytics', [BarberApiController::class, 'analytics']);
    Route::get('/barber/analytics', [BarberApiController::class, 'analytics']);

    Route::get('/barbers/account', [BarberApiController::class, 'account']);
    Route::get('/barber/account', [BarberApiController::class, 'account']);
    Route::post('/barbers/account', [BarberApiController::class, 'updateAccount']);
    Route::put('/barbers/account', [BarberApiController::class, 'updateAccount']);
    Route::patch('/barbers/account', [BarberApiController::class, 'updateAccount']);
    Route::patch('/barber/account', [BarberApiController::class, 'updateAccount']);
    Route::put('/barber/account', [BarberApiController::class, 'updateAccount']);
    Route::patch('/barbers/account/username', [BarberApiController::class, 'updateUsername']);

    Route::get('/barbers/schedule', [BarberApiController::class, 'schedule']);
    Route::get('/barber/schedule', [BarberApiController::class, 'schedule']);
    Route::put('/barbers/schedule', [BarberApiController::class, 'updateSchedule']);
    Route::put('/barber/schedule', [BarberApiController::class, 'updateSchedule']);

    Route::get('/barbers/blocked-periods', [BarberApiController::class, 'blockedPeriods']);
    Route::post('/barbers/blocked-periods', [BarberApiController::class, 'storeBlockedPeriod']);
    Route::delete('/barbers/blocked-periods/{id}', [BarberApiController::class, 'deleteBlockedPeriod'])->whereNumber('id');

    Route::patch('/barber/my-status', [BarberApiController::class, 'updateMyStatus']);
    Route::patch('/barbers/chair-status', [BarberApiController::class, 'updateChairStatus']);

    // Barber Appointments & Bookings
    Route::get('/barber/appointments', [AppointmentApiController::class, 'index']);
    Route::get('/barber/bookings', [AppointmentApiController::class, 'index']);
    Route::patch('/barber/appointments/{id}/complete', [AppointmentApiController::class, 'complete'])->whereNumber('id');
    Route::patch('/barber/appointments/{id}/no-show', [AppointmentApiController::class, 'noShow'])->whereNumber('id');
    Route::patch('/barber/appointments/{id}/approve', [AppointmentApiController::class, 'approve'])->whereNumber('id');
    Route::patch('/barber/appointments/{id}/cancel', [AppointmentApiController::class, 'cancel'])->whereNumber('id');
    Route::post('/barber/appointments/walk-in', [AppointmentApiController::class, 'storeWalkIn']);
    Route::post('/barber/bookings/{id}/verify-payment', [PaymentApiController::class, 'verifyPayment'])->whereNumber('id');

    // Barber Notifications
    Route::get('/barber/notifications', [NotificationController::class, 'index']);
    Route::post('/barber/notifications', [NotificationController::class, 'store']);

    // Barber Services
    Route::get('/barber/services', [ServiceApiController::class, 'index']);
    Route::post('/barber/services', [ServiceApiController::class, 'store']);
    Route::put('/barber/services/{id}', [ServiceApiController::class, 'update'])->whereNumber('id');
    Route::delete('/barber/services/{id}', [ServiceApiController::class, 'destroy'])->whereNumber('id');

    // Barber Gallery
    Route::get('/barber/gallery', [GalleryApiController::class, 'index']);
    Route::post('/barber/gallery', [GalleryApiController::class, 'store']);
    Route::post('/barber/gallery/{id}', [GalleryApiController::class, 'update'])->whereNumber('id');
    Route::delete('/barber/gallery/{id}', [GalleryApiController::class, 'destroy'])->whereNumber('id');

    // Barber Blog
    Route::get('/barber/blog', [BlogApiController::class, 'index']);
    Route::post('/barber/blog', [BlogApiController::class, 'store']);
    Route::post('/barber/blog/{id}', [BlogApiController::class, 'update'])->whereNumber('id');
    Route::delete('/barber/blog/{id}', [BlogApiController::class, 'destroy'])->whereNumber('id');

    // -------------------------------------------------------------------------
    // General Notifications
    // -------------------------------------------------------------------------
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->whereNumber('id');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->whereNumber('id');
    Route::get('/notification-settings', [NotificationController::class, 'getNotificationSettings']);
    Route::post('/notification-settings', [NotificationController::class, 'updateNotificationSettings']);
    Route::post('/notifications/device-token', [NotificationController::class, 'registerDeviceToken']);
    Route::delete('/notifications/device-token', [NotificationController::class, 'deleteDeviceToken']);
});
