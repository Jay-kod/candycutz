<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AccountApiController;
use App\Http\Controllers\Api\V1\AppointmentApiController;
use App\Http\Controllers\Api\V1\AvailabilityApiController;
use App\Http\Controllers\Api\V1\BarberApiController;
use App\Http\Controllers\Api\V1\BlogApiController;
use App\Http\Controllers\Api\V1\GalleryApiController;
use App\Http\Controllers\Api\V1\HealthApiController;
use App\Http\Controllers\Api\V1\PaymentApiController;
use App\Http\Controllers\Api\V1\PaymentWebhookApiController;
use App\Http\Controllers\Api\V1\ReceiptApiController;
use App\Http\Controllers\Api\V1\ServiceApiController;
use App\Http\Controllers\Api\V1\ServiceZoneApiController;
use App\Http\Controllers\Api\V1\TestimonialApiController;
use App\Http\Controllers\Api\V1\WishlistApiController;
use App\Http\Controllers\Api\V1\AdminApiController;
use App\Http\Controllers\Api\V1\SuperAdminApiController;
use App\Http\Controllers\Api\V1\PublicApiController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Api\V1\AuthApiController;
use Illuminate\Support\Facades\Route;

// Public Canonical Endpoints
Route::get('/health', [HealthApiController::class, 'health']);
Route::get('/services', [ServiceApiController::class, 'index']);
Route::get('/service-categories', [ServiceApiController::class, 'categories']);
Route::get('/services/{idOrSlug}', [ServiceApiController::class, 'show']);
Route::get('/barbers', [BarberApiController::class, 'index']);
Route::get('/availability', [AvailabilityApiController::class, 'index']);
Route::get('/service-zones', [ServiceZoneApiController::class, 'index']);
Route::get('/gallery', [GalleryApiController::class, 'index']);
Route::get('/gallery/{id}', [GalleryApiController::class, 'show'])->whereNumber('id');
Route::get('/testimonials', [TestimonialApiController::class, 'index']);
Route::get('/blog', [BlogApiController::class, 'index']);
Route::get('/blog/{slug}', [BlogApiController::class, 'show']);

// Auth Endpoints
Route::post('/auth/social-login', [AuthApiController::class, 'socialLogin']);
Route::post('/auth/login', [AuthApiController::class, 'login']);
Route::post('/auth/register', [AuthApiController::class, 'register']);
Route::post('/auth/forgot-password', [AuthApiController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthApiController::class, 'resetPassword']);
Route::post('/payments/webhook', [PaymentWebhookApiController::class, 'handle']);

// Authenticated Canonical Endpoints
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    Route::get('/auth/me', [AuthApiController::class, 'me']);
    
    // Account / Customer
    Route::get('/account/dashboard', [AccountApiController::class, 'dashboard']);
    Route::get('/account/profile', [AccountApiController::class, 'profile']);
    Route::post('/account/profile', [AccountApiController::class, 'updateProfile']);
    Route::get('/account/analytics', [AccountApiController::class, 'analytics']);
    Route::get('/account/my-codes', [AccountApiController::class, 'myCodes']);
    
    // Wishlist
    Route::get('/wishlist', [WishlistApiController::class, 'index']);
    Route::post('/wishlist', [WishlistApiController::class, 'store']);
    Route::delete('/wishlist/remove', [WishlistApiController::class, 'destroyByType']);
    Route::delete('/wishlist/{id}', [WishlistApiController::class, 'destroy'])->whereNumber('id');

    // Payments
    Route::post('/payments/checkout', [PaymentApiController::class, 'checkout']);
    Route::get('/payments/appointments/{appointmentId}/details', [PaymentApiController::class, 'paymentDetails'])->whereNumber('appointmentId');
    Route::post('/payments/appointments/{appointmentId}/receipt', [PaymentApiController::class, 'uploadReceipt'])->whereNumber('appointmentId');
    Route::get('/payments/appointments/{appointmentId}/receipt', [ReceiptApiController::class, 'show'])->whereNumber('appointmentId');

    // Specific Barber Operations (declared before /barbers/{id} wildcard)
    Route::get('/barbers/dashboard', [BarberApiController::class, 'dashboard']);
    Route::get('/barbers/analytics', [BarberApiController::class, 'analytics']);
    Route::get('/barbers/account', [BarberApiController::class, 'account']);
    Route::put('/barbers/account', [BarberApiController::class, 'updateAccount']);
    Route::patch('/barbers/chair-status', [BarberApiController::class, 'updateChairStatus']);
    Route::get('/barbers/my-appointments', [AppointmentApiController::class, 'index']);
    Route::get('/barbers/schedule', [BarberApiController::class, 'schedule']);
    Route::put('/barbers/schedule', [BarberApiController::class, 'updateSchedule']);
    Route::get('/barbers/blocked-periods', [BarberApiController::class, 'blockedPeriods']);
    Route::post('/barbers/blocked-periods', [BarberApiController::class, 'storeBlockedPeriod']);
    Route::delete('/barbers/blocked-periods/{id}', [BarberApiController::class, 'deleteBlockedPeriod'])->whereNumber('id');
    Route::post('/barbers/walk-in', [AppointmentApiController::class, 'storeWalkIn']);
    Route::post('/barbers/gallery', [GalleryApiController::class, 'store']);
    Route::delete('/barbers/gallery/{id}', [GalleryApiController::class, 'destroy'])->whereNumber('id');
    Route::post('/barbers/services', [ServiceApiController::class, 'store']);
    Route::put('/barbers/services/{id}', [ServiceApiController::class, 'update'])->whereNumber('id');
    Route::delete('/barbers/services/{id}', [ServiceApiController::class, 'destroy'])->whereNumber('id');
    
    // Blog Management
    Route::post('/blog', [BlogApiController::class, 'store']);
    Route::put('/blog/{id}', [BlogApiController::class, 'update'])->whereNumber('id');
    Route::delete('/blog/{id}', [BlogApiController::class, 'destroy'])->whereNumber('id');

    // Blog Reactions
    Route::post('/blog/{id}/react', [BlogApiController::class, 'react'])->whereNumber('id');
    Route::delete('/blog/{id}/react', [BlogApiController::class, 'removeReaction'])->whereNumber('id');

    // Appointments Lifecycle (static routes before {id} wildcard)
    Route::get('/appointments', [AppointmentApiController::class, 'index']);
    Route::post('/appointments', [AppointmentApiController::class, 'store']);
    Route::post('/appointments/walk-in', [AppointmentApiController::class, 'storeWalkIn']);
    Route::get('/appointments/{id}', [AppointmentApiController::class, 'show'])->whereNumber('id');
    Route::match(['post', 'patch'], '/appointments/{id}/cancel', [AppointmentApiController::class, 'cancel'])->whereNumber('id');
    Route::patch('/appointments/{id}/status', [AppointmentApiController::class, 'updateStatus'])->whereNumber('id');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->whereNumber('id');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->whereNumber('id');
    Route::get('/notification-settings', [NotificationController::class, 'getNotificationSettings']);
    Route::post('/notification-settings', [NotificationController::class, 'updateNotificationSettings']);
});

// Single barber lookup (after specific barber routes, constrained to numeric ID)
Route::get('/barbers/{id}', [BarberApiController::class, 'show'])->whereNumber('id');



// Admin Routes
Route::prefix('admin')->middleware(['auth:sanctum', 'check.role:admin,super_admin'])->group(function () {
    Route::get('/dashboard', [AdminApiController::class, 'dashboard']);
    Route::get('/settings', [AdminApiController::class, 'settings']);
    Route::post('/settings', [AdminApiController::class, 'updateSettings']);
    Route::post('/test-email', [AdminApiController::class, 'testEmail']);
    
    Route::get('/appointments', [AdminApiController::class, 'appointments']);
    Route::post('/appointments/walk-in', [AdminApiController::class, 'createWalkIn']);
    Route::patch('/appointments/{appointment}/approve', [AdminApiController::class, 'approveAppointment']);
    Route::patch('/appointments/{appointment}/force-approve', [AdminApiController::class, 'forceApproveAppointment']);
    Route::patch('/appointments/{appointment}/cancel', [AdminApiController::class, 'cancelAppointment']);
    
    Route::get('/services', [AdminApiController::class, 'services']);
    Route::post('/services', [AdminApiController::class, 'storeService']);
    Route::match(['put', 'patch', 'post'], '/services/{service}', [AdminApiController::class, 'updateService']);
    Route::delete('/services/{service}', [AdminApiController::class, 'deleteService']);
    
    Route::get('/service-categories', [AdminApiController::class, 'serviceCategories']);
    Route::post('/service-categories', [AdminApiController::class, 'storeServiceCategory']);
    Route::match(['put', 'patch'], '/service-categories/{serviceCategory}', [AdminApiController::class, 'updateServiceCategory']);
    Route::delete('/service-categories/{serviceCategory}', [AdminApiController::class, 'deleteServiceCategory']);
    
    Route::get('/gallery', [AdminApiController::class, 'gallery']);
    Route::post('/gallery', [AdminApiController::class, 'storeGallery']);
    Route::match(['put', 'patch', 'post'], '/gallery/{gallery}', [AdminApiController::class, 'updateGallery']);
    Route::delete('/gallery/{gallery}', [AdminApiController::class, 'deleteGallery']);
    
    Route::get('/testimonials', [AdminApiController::class, 'testimonials']);
    Route::match(['put', 'patch'], '/testimonials/{testimonial}', [AdminApiController::class, 'updateTestimonial']);
    Route::patch('/testimonials/{testimonial}/approve', [AdminApiController::class, 'approveTestimonial']);
    Route::patch('/testimonials/{testimonial}/feature', [AdminApiController::class, 'featureTestimonial']);
    Route::delete('/testimonials/{testimonial}', [AdminApiController::class, 'deleteTestimonial']);
    
    Route::get('/blog', [AdminApiController::class, 'blogPosts']);
    Route::post('/blog', [AdminApiController::class, 'storeBlogPost']);
    Route::match(['put', 'patch', 'post'], '/blog/{blogPost}', [AdminApiController::class, 'updateBlogPost']);
    Route::delete('/blog/{blogPost}', [AdminApiController::class, 'deleteBlogPost']);
    
    Route::get('/working-hours', [AdminApiController::class, 'workingHours']);
    Route::put('/working-hours/{barberId}', [AdminApiController::class, 'updateBarberWorkingHours']);
    Route::post('/working-hours', [AdminApiController::class, 'storeWorkingHour']);
    Route::match(['put', 'patch'], '/working-hours/{workingHour}', [AdminApiController::class, 'updateWorkingHour']);
    Route::delete('/working-hours/{workingHour}', [AdminApiController::class, 'deleteWorkingHour']);
    
    Route::get('/holidays', [AdminApiController::class, 'holidays']);
    Route::post('/holidays', [AdminApiController::class, 'storeHoliday']);
    Route::match(['put', 'patch'], '/holidays/{holiday}', [AdminApiController::class, 'updateHoliday']);
    Route::delete('/holidays/{holiday}', [AdminApiController::class, 'deleteHoliday']);
    
    Route::get('/barbers', [AdminApiController::class, 'barbers']);
    Route::post('/barbers', [AdminApiController::class, 'storeBarber']);
    Route::put('/barbers/{barber}', [AdminApiController::class, 'updateBarber']);
    Route::patch('/barbers/{barber}/status', [AdminApiController::class, 'updateBarberStatus']);
    Route::delete('/barbers/{barber}', [AdminApiController::class, 'deleteBarber']);
    
    Route::get('/customers', [AdminApiController::class, 'customers']);
    Route::get('/customers/{id}', [AdminApiController::class, 'customerProfile']);
    
    Route::get('/logs', [AdminApiController::class, 'logs']);
    
    Route::get('/verifications/stats', [AdminApiController::class, 'verificationStats']);
    Route::get('/verifications', [AdminApiController::class, 'verifications']);
    Route::patch('/verifications/{id}/verify', [AdminApiController::class, 'verifyAppointment']);
    
    Route::get('/reports', [AdminApiController::class, 'reports']);
    Route::get('/analytics', [AdminApiController::class, 'analytics']);
});

// SuperAdmin Routes
Route::prefix('super-admin')->middleware(['auth:sanctum', 'check.role:super_admin'])->group(function () {
    Route::get('/dashboard', [SuperAdminApiController::class, 'dashboard']);
    Route::get('/users', [SuperAdminApiController::class, 'users']);
    Route::post('/users', [SuperAdminApiController::class, 'storeUser']);
    Route::patch('/users/{user}', [SuperAdminApiController::class, 'updateUser']);
    Route::patch('/users/{user}/activate', [SuperAdminApiController::class, 'activateUser']);
    Route::patch('/users/{user}/deactivate', [SuperAdminApiController::class, 'deactivateUser']);
    Route::delete('/users/{user}', [SuperAdminApiController::class, 'deleteUser']);
    Route::get('/settings', [SuperAdminApiController::class, 'settings']);
    Route::post('/settings', [SuperAdminApiController::class, 'updateSettings']);
    Route::get('/audit-logs', [SuperAdminApiController::class, 'auditLogs']);
});
