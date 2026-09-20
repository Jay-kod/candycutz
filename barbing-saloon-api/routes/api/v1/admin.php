<?php

use App\Http\Controllers\Api\V1\AdminApiController;
use App\Http\Controllers\Api\V1\AppointmentApiController;
use App\Http\Controllers\Api\V1\BarberApiController;
use App\Http\Controllers\Api\V1\BlogApiController;
use App\Http\Controllers\Api\V1\GalleryApiController;
use App\Http\Controllers\Api\V1\GateApiController;
use App\Http\Controllers\Api\V1\SuperAdminApiController;
use App\Http\Controllers\Api\V1\TestimonialApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'check.role:admin,super_admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminApiController::class, 'dashboard']);
    Route::get('/admin/gate/metrics', [GateApiController::class, 'metrics']);
    Route::get('/admin/gate/logs', [GateApiController::class, 'logs']);
    Route::post('/admin/gate/flush', [GateApiController::class, 'flush']);
    Route::get('/admin/settings', [AdminApiController::class, 'settings']);
    Route::post('/admin/settings', [AdminApiController::class, 'updateSettings']);
    Route::post('/admin/test-email', [AdminApiController::class, 'testEmail']);
    Route::get('/admin/logs', [AdminApiController::class, 'logs']);
    Route::get('/admin/verifications/stats', [AdminApiController::class, 'verificationStats']);
    Route::get('/admin/verifications', [AdminApiController::class, 'verifications']);
    Route::patch('/admin/verifications/{id}/verify', [AdminApiController::class, 'verifyAppointment']);
    Route::get('/admin/reports', [AdminApiController::class, 'reports']);
    Route::get('/admin/analytics', [AdminApiController::class, 'analytics']);
    Route::get('/admin/customers', [AdminApiController::class, 'customers']);
    Route::get('/admin/customers/{id}', [AdminApiController::class, 'customerProfile']);
    Route::get('/admin/working-hours', [AdminApiController::class, 'workingHours']);
    Route::put('/admin/working-hours/{barberId}', [AdminApiController::class, 'updateBarberWorkingHours']);
    Route::post('/admin/working-hours', [AdminApiController::class, 'storeWorkingHour']);
    Route::match(['put', 'patch'], '/admin/working-hours/{workingHour}', [AdminApiController::class, 'updateWorkingHour']);
    Route::delete('/admin/working-hours/{workingHour}', [AdminApiController::class, 'deleteWorkingHour']);
    Route::get('/admin/holidays', [AdminApiController::class, 'holidays']);
    Route::post('/admin/holidays', [AdminApiController::class, 'storeHoliday']);
    Route::match(['put', 'patch'], '/admin/holidays/{holiday}', [AdminApiController::class, 'updateHoliday']);
    Route::delete('/admin/holidays/{holiday}', [AdminApiController::class, 'deleteHoliday']);

    // Admin Appointments Management
    Route::get('/admin/appointments', [AppointmentApiController::class, 'index']);
    Route::post('/admin/appointments', [AppointmentApiController::class, 'store']);
    Route::post('/admin/appointments/walk-in', [AppointmentApiController::class, 'storeWalkIn']);
    Route::get('/admin/appointments/{id}', [AppointmentApiController::class, 'show'])->whereNumber('id');
    Route::match(['post', 'patch'], '/admin/appointments/{id}/cancel', [AppointmentApiController::class, 'cancel'])->whereNumber('id');
    Route::patch('/admin/appointments/{id}/status', [AppointmentApiController::class, 'updateStatus'])->whereNumber('id');
    Route::patch('/admin/appointments/{id}/approve', [AppointmentApiController::class, 'approve'])->whereNumber('id');
    Route::patch('/admin/appointments/{id}/force-approve', [AppointmentApiController::class, 'forceApprove'])->whereNumber('id');

    // Admin Barber Management
    Route::get('/admin/barbers', [BarberApiController::class, 'index']);
    Route::post('/admin/barbers', [BarberApiController::class, 'store']);
    Route::get('/admin/barbers/{id}', [BarberApiController::class, 'show'])->whereNumber('id');
    Route::put('/admin/barbers/{id}', [BarberApiController::class, 'update'])->whereNumber('id');
    Route::patch('/admin/barbers/{id}/status', [BarberApiController::class, 'updateStatus'])->whereNumber('id');
    Route::delete('/admin/barbers/{id}', [BarberApiController::class, 'destroy'])->whereNumber('id');

    // Admin Gallery Management
    Route::get('/admin/gallery', [GalleryApiController::class, 'index']);
    Route::post('/admin/gallery', [GalleryApiController::class, 'store']);
    Route::match(['post', 'put'], '/admin/gallery/{id}', [GalleryApiController::class, 'update'])->whereNumber('id');
    Route::delete('/admin/gallery/{id}', [GalleryApiController::class, 'destroy'])->whereNumber('id');

    // Admin Testimonials Management
    Route::get('/admin/testimonials', [TestimonialApiController::class, 'index']);
    Route::match(['post', 'put'], '/admin/testimonials/{id}', [TestimonialApiController::class, 'update'])->whereNumber('id');
    Route::patch('/admin/testimonials/{id}/approve', [TestimonialApiController::class, 'approve'])->whereNumber('id');
    Route::patch('/admin/testimonials/{id}/feature', [TestimonialApiController::class, 'feature'])->whereNumber('id');
    Route::delete('/admin/testimonials/{id}', [TestimonialApiController::class, 'destroy'])->whereNumber('id');

    // Admin Blog Management
    Route::get('/admin/blog', [BlogApiController::class, 'index']);
    Route::post('/admin/blog', [BlogApiController::class, 'store']);
    Route::match(['post', 'put'], '/admin/blog/{id}', [BlogApiController::class, 'update'])->whereNumber('id');
    Route::delete('/admin/blog/{id}', [BlogApiController::class, 'destroy'])->whereNumber('id');
});

Route::middleware(['auth:sanctum', 'check.role:super_admin'])->group(function () {
    // With hyphen
    Route::get('/super-admin/dashboard', [SuperAdminApiController::class, 'dashboard']);
    Route::get('/super-admin/users', [SuperAdminApiController::class, 'users']);
    Route::post('/super-admin/users', [SuperAdminApiController::class, 'storeUser']);
    Route::patch('/super-admin/users/{user}', [SuperAdminApiController::class, 'updateUser']);
    Route::patch('/super-admin/users/{user}/activate', [SuperAdminApiController::class, 'activateUser']);
    Route::patch('/super-admin/users/{user}/deactivate', [SuperAdminApiController::class, 'deactivateUser']);
    Route::delete('/super-admin/users/{user}', [SuperAdminApiController::class, 'deleteUser']);
    Route::get('/super-admin/settings', [SuperAdminApiController::class, 'settings']);
    Route::post('/super-admin/settings', [SuperAdminApiController::class, 'updateSettings']);
    Route::get('/super-admin/audit-logs', [SuperAdminApiController::class, 'auditLogs']);

    // Without hyphen (direct match for /superadmin/* routes)
    Route::get('/superadmin/dashboard', [SuperAdminApiController::class, 'dashboard']);
    Route::get('/superadmin/users', [SuperAdminApiController::class, 'users']);
    Route::post('/superadmin/users', [SuperAdminApiController::class, 'storeUser']);
    Route::patch('/superadmin/users/{user}', [SuperAdminApiController::class, 'updateUser']);
    Route::patch('/superadmin/users/{user}/activate', [SuperAdminApiController::class, 'activateUser']);
    Route::patch('/superadmin/users/{user}/deactivate', [SuperAdminApiController::class, 'deactivateUser']);
    Route::delete('/superadmin/users/{user}', [SuperAdminApiController::class, 'deleteUser']);
    Route::get('/superadmin/settings', [SuperAdminApiController::class, 'settings']);
    Route::post('/superadmin/settings', [SuperAdminApiController::class, 'updateSettings']);
    Route::get('/superadmin/audit-logs', [SuperAdminApiController::class, 'auditLogs']);
});
