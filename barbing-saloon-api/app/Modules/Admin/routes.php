<?php

use App\Core\Http\Controllers\NotificationController;
use App\Modules\Admin\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/settings', [AdminController::class, 'settings']);
    Route::post('/settings', [AdminController::class, 'updateSettings']);
    Route::post('/test-email', [AdminController::class, 'testEmail']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Appointments & Walk-ins
    Route::get('/appointments', [AdminController::class, 'appointments']);
    Route::post('/appointments/walk-in', [AdminController::class, 'createWalkIn']);
    Route::patch('/appointments/{appointment}/approve', [AdminController::class, 'approveAppointment']);
    Route::patch('/appointments/{appointment}/force-approve', [AdminController::class, 'forceApproveAppointment']);
    Route::patch('/appointments/{appointment}/cancel', [AdminController::class, 'cancelAppointment']);

    // Services
    Route::get('/services', [AdminController::class, 'services']);
    Route::post('/services', [AdminController::class, 'storeService']);
    Route::match(['put', 'patch', 'post'], '/services/{service}', [AdminController::class, 'updateService']);
    Route::delete('/services/{service}', [AdminController::class, 'deleteService']);

    // Service Categories
    Route::get('/service-categories', [AdminController::class, 'serviceCategories']);
    Route::post('/service-categories', [AdminController::class, 'storeServiceCategory']);
    Route::match(['put', 'patch'], '/service-categories/{serviceCategory}', [AdminController::class, 'updateServiceCategory']);
    Route::delete('/service-categories/{serviceCategory}', [AdminController::class, 'deleteServiceCategory']);

    // Gallery
    Route::get('/gallery', [AdminController::class, 'gallery']);
    Route::post('/gallery', [AdminController::class, 'storeGallery']);
    Route::match(['put', 'patch', 'post'], '/gallery/{gallery}', [AdminController::class, 'updateGallery']);
    Route::delete('/gallery/{gallery}', [AdminController::class, 'deleteGallery']);

    // Testimonials
    Route::get('/testimonials', [AdminController::class, 'testimonials']);
    Route::match(['put', 'patch'], '/testimonials/{testimonial}', [AdminController::class, 'updateTestimonial']);
    Route::patch('/testimonials/{testimonial}/approve', [AdminController::class, 'approveTestimonial']);
    Route::patch('/testimonials/{testimonial}/feature', [AdminController::class, 'featureTestimonial']);
    Route::delete('/testimonials/{testimonial}', [AdminController::class, 'deleteTestimonial']);

    // Blog
    Route::get('/blog', [AdminController::class, 'blogPosts']);
    Route::post('/blog', [AdminController::class, 'storeBlogPost']);
    Route::match(['put', 'patch', 'post'], '/blog/{blogPost}', [AdminController::class, 'updateBlogPost']);
    Route::delete('/blog/{blogPost}', [AdminController::class, 'deleteBlogPost']);

    // Working Hours
    Route::get('/working-hours', [AdminController::class, 'workingHours']);
    Route::put('/working-hours/{barberId}', [AdminController::class, 'updateBarberWorkingHours']);
    Route::post('/working-hours', [AdminController::class, 'storeWorkingHour']);
    Route::match(['put', 'patch'], '/working-hours/{workingHour}', [AdminController::class, 'updateWorkingHour']);
    Route::delete('/working-hours/{workingHour}', [AdminController::class, 'deleteWorkingHour']);

    // Holidays
    Route::get('/holidays', [AdminController::class, 'holidays']);
    Route::post('/holidays', [AdminController::class, 'storeHoliday']);
    Route::match(['put', 'patch'], '/holidays/{holiday}', [AdminController::class, 'updateHoliday']);
    Route::delete('/holidays/{holiday}', [AdminController::class, 'deleteHoliday']);

    // Barbers Management
    Route::get('/barbers', [AdminController::class, 'barbers']);
    Route::post('/barbers', [AdminController::class, 'storeBarber']);
    Route::put('/barbers/{barber}', [AdminController::class, 'updateBarber']);
    Route::patch('/barbers/{barber}/status', [AdminController::class, 'updateBarberStatus']);
    Route::delete('/barbers/{barber}', [AdminController::class, 'deleteBarber']);

    // Customers Management
    Route::get('/customers', [AdminController::class, 'customers']);
    Route::get('/customers/{id}', [AdminController::class, 'customerProfile']);

    // System Logs
    Route::get('/logs', [AdminController::class, 'logs']);

    // Verifications
    Route::get('/verifications/stats', [AdminController::class, 'verificationStats']);
    Route::get('/verifications', [AdminController::class, 'verifications']);
    Route::patch('/verifications/{id}/verify', [AdminController::class, 'verifyAppointment']);

    // Reports & Analytics
    Route::get('/reports', [AdminController::class, 'reports']);
    Route::get('/analytics', [AdminController::class, 'analytics']);
});