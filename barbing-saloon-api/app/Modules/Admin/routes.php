<?php

use App\Modules\Admin\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/settings', [AdminController::class, 'settings']);
    Route::post('/settings', [AdminController::class, 'updateSettings']);

    Route::get('/appointments', [AdminController::class, 'appointments']);
    Route::patch('/appointments/{appointment}/approve', [AdminController::class, 'approveAppointment']);
    Route::patch('/appointments/{appointment}/cancel', [AdminController::class, 'cancelAppointment']);

    Route::get('/services', [AdminController::class, 'services']);
    Route::post('/services', [AdminController::class, 'storeService']);
    Route::patch('/services/{service}', [AdminController::class, 'updateService']);
    Route::delete('/services/{service}', [AdminController::class, 'deleteService']);

    Route::get('/service-categories', [AdminController::class, 'serviceCategories']);
    Route::post('/service-categories', [AdminController::class, 'storeServiceCategory']);
    Route::patch('/service-categories/{serviceCategory}', [AdminController::class, 'updateServiceCategory']);
    Route::delete('/service-categories/{serviceCategory}', [AdminController::class, 'deleteServiceCategory']);

    Route::get('/gallery', [AdminController::class, 'gallery']);
    Route::post('/gallery', [AdminController::class, 'storeGallery']);
    Route::patch('/gallery/{gallery}', [AdminController::class, 'updateGallery']);
    Route::delete('/gallery/{gallery}', [AdminController::class, 'deleteGallery']);

    Route::get('/testimonials', [AdminController::class, 'testimonials']);
    Route::patch('/testimonials/{testimonial}/approve', [AdminController::class, 'approveTestimonial']);
    Route::patch('/testimonials/{testimonial}/feature', [AdminController::class, 'featureTestimonial']);

    Route::get('/blog', [AdminController::class, 'blogPosts']);
    Route::post('/blog', [AdminController::class, 'storeBlogPost']);
    Route::patch('/blog/{blogPost}', [AdminController::class, 'updateBlogPost']);
    Route::delete('/blog/{blogPost}', [AdminController::class, 'deleteBlogPost']);

    Route::get('/working-hours', [AdminController::class, 'workingHours']);
    Route::post('/working-hours', [AdminController::class, 'storeWorkingHour']);
    Route::patch('/working-hours/{workingHour}', [AdminController::class, 'updateWorkingHour']);
    Route::delete('/working-hours/{workingHour}', [AdminController::class, 'deleteWorkingHour']);

    Route::get('/holidays', [AdminController::class, 'holidays']);
    Route::post('/holidays', [AdminController::class, 'storeHoliday']);
    Route::patch('/holidays/{holiday}', [AdminController::class, 'updateHoliday']);
    Route::delete('/holidays/{holiday}', [AdminController::class, 'deleteHoliday']);

    Route::get('/reports', [AdminController::class, 'reports']);
});