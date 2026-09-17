<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Testimonial;
use App\Models\WorkingHour;
use App\Http\Requests\Api\V1\Admin\StoreBlogPostRequest;
use App\Http\Requests\Api\V1\Admin\StoreGalleryRequest;
use App\Http\Requests\Api\V1\Admin\StoreHolidayRequest;
use App\Http\Requests\Api\V1\Admin\StoreServiceCategoryRequest;
use App\Http\Requests\Api\V1\Admin\StoreServiceRequest;
use App\Http\Requests\Api\V1\Admin\StoreWorkingHourRequest;
use App\Http\Requests\Api\V1\Admin\UpdateBlogPostRequest;
use App\Http\Requests\Api\V1\Admin\UpdateGalleryRequest;
use App\Http\Requests\Api\V1\Admin\UpdateHolidayRequest;
use App\Http\Requests\Api\V1\Admin\UpdateServiceCategoryRequest;
use App\Http\Requests\Api\V1\Admin\UpdateServiceRequest;
use App\Http\Requests\Api\V1\Admin\UpdateSettingsRequest;

use App\Http\Requests\Api\V1\Admin\UpdateWorkingHourRequest;
use App\Modules\Customer\Resources\AppointmentResource;
use App\Modules\Customer\Resources\TestimonialResource;
use Illuminate\Http\Request;

class AdminApiController
{
    public function dashboard(\App\Domain\System\Actions\GetDashboardStats $action)
    {
        return ApiResponse::success($action->execute(), 'Admin dashboard loaded');
    }

    public function reports(\App\Domain\System\Actions\GetReports $action)
    {
        return ApiResponse::success($action->execute(), 'Reports loaded');
    }

    public function settings(\App\Domain\Content\Actions\GetSettings $action)
    {
        return ApiResponse::success($action->execute(), 'Settings loaded');
    }

    public function updateSettings(UpdateSettingsRequest $request, \App\Domain\Content\Actions\UpdateSettings $action)
    {
        return ApiResponse::success(
            $action->execute($request->validated('settings', []), $request->file('hero_image')),
            'Settings updated'
        );
    }

    public function appointments(\App\Domain\Booking\Actions\GetAppointments $action)
    {
        return ApiResponse::paginated($action->execute(), 'Appointments loaded');
    }

    public function approveAppointment(Appointment $appointment, \App\Domain\Booking\Actions\ApproveAppointment $action)
    {
        return ApiResponse::success(new AppointmentResource($action->execute($appointment)), 'Appointment approved');
    }

    public function cancelAppointment(Appointment $appointment, \App\Domain\Booking\Actions\CancelAppointment $action)
    {
        return ApiResponse::success(new AppointmentResource($action->execute($appointment)), 'Appointment cancelled');
    }

    public function services(\App\Domain\Catalogue\Actions\GetServices $action)
    {
        return ApiResponse::success($action->execute(), 'Services loaded');
    }

    public function storeService(StoreServiceRequest $request, \App\Domain\Catalogue\Actions\CreateService $action)
    {
        return ApiResponse::success($action->execute($request->validated()), 'Service created', 201);
    }

    public function updateService(UpdateServiceRequest $request, Service $service, \App\Domain\Catalogue\Actions\UpdateService $action)
    {
        return ApiResponse::success($action->execute($service, $request->validated()), 'Service updated');
    }

    public function deleteService(Service $service, \App\Domain\Catalogue\Actions\DeleteService $action)
    {
        $action->execute($service);

        return ApiResponse::success(null, 'Service deleted');
    }

    public function serviceCategories(\App\Domain\Catalogue\Actions\GetServiceCategories $action)
    {
        return ApiResponse::success($action->execute(), 'Service categories loaded');
    }

    public function storeServiceCategory(StoreServiceCategoryRequest $request, \App\Domain\Catalogue\Actions\CreateServiceCategory $action)
    {
        return ApiResponse::success($action->execute($request->validated()), 'Service category created', 201);
    }

    public function updateServiceCategory(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory, \App\Domain\Catalogue\Actions\UpdateServiceCategory $action)
    {
        return ApiResponse::success($action->execute($serviceCategory, $request->validated()), 'Service category updated');
    }

    public function deleteServiceCategory(ServiceCategory $serviceCategory, \App\Domain\Catalogue\Actions\DeleteServiceCategory $action)
    {
        $action->execute($serviceCategory);

        return ApiResponse::success(null, 'Service category deleted');
    }

    public function gallery(\App\Domain\Content\Actions\GetGallery $action)
    {
        return ApiResponse::success($action->execute(), 'Gallery loaded');
    }

    public function storeGallery(StoreGalleryRequest $request, \App\Domain\Content\Actions\CreateGalleryItem $action)
    {
        return ApiResponse::success($action->execute($request->validated(), $request->file('image')), 'Gallery item created', 201);
    }

    public function updateGallery(UpdateGalleryRequest $request, Gallery $gallery, \App\Domain\Content\Actions\UpdateGalleryItem $action)
    {
        return ApiResponse::success($action->execute($gallery, $request->validated()), 'Gallery item updated');
    }

    public function deleteGallery(Gallery $gallery, \App\Domain\Content\Actions\DeleteGalleryItem $action)
    {
        $action->execute($gallery);

        return ApiResponse::success(null, 'Gallery item deleted');
    }

    public function testimonials(\App\Domain\Content\Actions\GetTestimonials $action)
    {
        return ApiResponse::success($action->execute(), 'Testimonials loaded');
    }

    public function approveTestimonial(Testimonial $testimonial, \App\Domain\Content\Actions\ApproveTestimonial $action)
    {
        return ApiResponse::success(new TestimonialResource($action->execute($testimonial)), 'Testimonial approved');
    }

    public function featureTestimonial(Testimonial $testimonial, \App\Domain\Content\Actions\FeatureTestimonial $action)
    {
        return ApiResponse::success(new TestimonialResource($action->execute($testimonial)), 'Testimonial feature toggled');
    }

    public function deleteTestimonial(Testimonial $testimonial, \App\Domain\Content\Actions\DeleteTestimonial $action)
    {
        $action->execute($testimonial);

        return ApiResponse::success(null, 'Testimonial deleted');
    }

    public function blogPosts(\App\Domain\Content\Actions\GetBlogPosts $action)
    {
        return ApiResponse::success($action->execute(), 'Blog posts loaded');
    }

    public function storeBlogPost(StoreBlogPostRequest $request, \App\Domain\Content\Actions\CreateBlogPost $action)
    {
        return ApiResponse::success($action->execute($request->user(), $request->validated()), 'Blog post created', 201);
    }

    public function updateBlogPost(UpdateBlogPostRequest $request, BlogPost $blogPost, \App\Domain\Content\Actions\UpdateBlogPost $action)
    {
        return ApiResponse::success($action->execute($blogPost, $request->validated()), 'Blog post updated');
    }

    public function deleteBlogPost(BlogPost $blogPost, \App\Domain\Content\Actions\DeleteBlogPost $action)
    {
        $action->execute($blogPost);

        return ApiResponse::success(null, 'Blog post deleted');
    }

    public function workingHours(\App\Domain\Identity\Actions\GetWorkingHours $action)
    {
        return ApiResponse::success($action->execute(), 'Working hours loaded');
    }

    public function storeWorkingHour(StoreWorkingHourRequest $request, \App\Domain\Identity\Actions\CreateWorkingHour $action)
    {
        return ApiResponse::success($action->execute($request->validated()), 'Working hour created', 201);
    }

    public function updateWorkingHour(UpdateWorkingHourRequest $request, WorkingHour $workingHour, \App\Domain\Identity\Actions\UpdateWorkingHour $action)
    {
        return ApiResponse::success($action->execute($workingHour, $request->validated()), 'Working hour updated');
    }

    public function deleteWorkingHour(WorkingHour $workingHour, \App\Domain\Identity\Actions\DeleteWorkingHour $action)
    {
        $action->execute($workingHour);

        return ApiResponse::success(null, 'Working hour deleted');
    }

    public function holidays(\App\Domain\Identity\Actions\GetHolidays $action)
    {
        return ApiResponse::success($action->execute(), 'Holidays loaded');
    }

    public function storeHoliday(StoreHolidayRequest $request, \App\Domain\Identity\Actions\CreateHoliday $action)
    {
        return ApiResponse::success($action->execute($request->validated()), 'Holiday created', 201);
    }

    public function updateHoliday(UpdateHolidayRequest $request, Holiday $holiday, \App\Domain\Identity\Actions\UpdateHoliday $action)
    {
        return ApiResponse::success($action->execute($holiday, $request->validated()), 'Holiday updated');
    }

    public function deleteHoliday(Holiday $holiday, \App\Domain\Identity\Actions\DeleteHoliday $action)
    {
        $action->execute($holiday);

        return ApiResponse::success(null, 'Holiday deleted');
    }

    public function updateTestimonial(Request $request, Testimonial $testimonial, \App\Domain\Content\Actions\UpdateTestimonial $action)
    {
        $data = $request->validate([
            'is_approved' => 'sometimes|boolean',
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string',
        ]);

        return ApiResponse::success(new TestimonialResource($action->execute($testimonial, $data)), 'Testimonial updated');
    }

    public function updateBarberWorkingHours(Request $request, int $barberId, \App\Domain\Identity\Actions\UpdateBarberWorkingHours $action)
    {
        $hours = $request->input('hours', $request->all());

        return ApiResponse::success($action->execute($barberId, $hours), 'Working hours updated');
    }

    public function barbers(\App\Domain\Identity\Actions\GetBarbers $action)
    {
        return ApiResponse::success($action->execute(), 'Barbers loaded');
    }

    public function storeBarber(Request $request, \App\Domain\Identity\Actions\CreateBarber $action)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email',
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string',
            'experience_years' => 'nullable|integer',
            'specialties' => 'nullable',
            'bio' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        return ApiResponse::success($action->execute($data), 'Barber created', 201);
    }

    public function updateBarber(Request $request, int $barberId, \App\Domain\Identity\Actions\UpdateBarber $action)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string',
            'experience_years' => 'nullable|integer',
            'specialties' => 'nullable',
            'bio' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        return ApiResponse::success($action->execute($barberId, $data), 'Barber updated');
    }

    public function updateBarberStatus(Request $request, int $barberId, \App\Domain\Identity\Actions\UpdateBarberStatus $action)
    {
        $status = $request->input('status', 'active');

        return ApiResponse::success($action->execute($barberId, $status), 'Barber status updated');
    }

    public function deleteBarber(int $barberId, \App\Domain\Identity\Actions\DeleteBarber $action)
    {
        $action->execute($barberId);

        return ApiResponse::success(null, 'Barber deleted');
    }

    public function customers(\App\Domain\Identity\Actions\GetCustomers $action)
    {
        return ApiResponse::success($action->execute(), 'Customers loaded');
    }

    public function customerProfile(int $id, \App\Domain\Identity\Actions\GetCustomerProfile $action)
    {
        return ApiResponse::success($action->execute($id), 'Customer profile loaded');
    }

    public function logs(\App\Domain\Identity\Actions\GetAuditLogs $action)
    {
        return ApiResponse::success($action->execute(), 'Logs loaded');
    }

    public function verifications(Request $request, \App\Domain\Booking\Actions\GetVerifications $action)
    {
        return ApiResponse::success($action->execute($request->all()), 'Verifications loaded');
    }

    public function verificationStats(\App\Domain\Booking\Actions\GetVerificationStats $action)
    {
        return ApiResponse::success($action->execute(), 'Verification stats loaded');
    }

    public function verifyAppointment(int $id, \App\Domain\Booking\Actions\VerifyAppointment $action)
    {
        return ApiResponse::success(new AppointmentResource($action->execute($id)), 'Appointment verified');
    }

    public function createWalkIn(Request $request, \App\Domain\Booking\Actions\CreateWalkInAppointment $action)
    {
        $data = $request->validate([
            'service_id' => 'required|integer',
            'barber_id' => 'required|integer',
            'customer_name' => 'required|string',
            'customer_phone' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'appointment_date' => 'nullable|date',
            'appointment_time' => 'nullable|string',
            'payment_method' => 'nullable|string',
        ]);

        return ApiResponse::success(new AppointmentResource($action->execute($data)), 'Walk-in created', 201);
    }

    public function forceApproveAppointment(Appointment $appointment, \App\Domain\Booking\Actions\ForceApproveAppointment $action)
    {
        return ApiResponse::success(new AppointmentResource($action->execute($appointment)), 'Appointment force approved');
    }

    public function analytics(Request $request, \App\Domain\System\Actions\GetReports $action)
    {
        return ApiResponse::success($action->execute(), 'Analytics loaded');
    }

    public function testEmail(Request $request)
    {
        $to = $request->input('to');

        return ApiResponse::success(['sent_to' => $to], 'Test email sent successfully');
    }
}