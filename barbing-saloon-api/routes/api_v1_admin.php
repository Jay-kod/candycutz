<?php

use App\Http\Controllers\Api\V1\AdminApiController;
use App\Http\Controllers\Api\V1\SuperAdminApiController;
use Illuminate\Support\Facades\Route;

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
