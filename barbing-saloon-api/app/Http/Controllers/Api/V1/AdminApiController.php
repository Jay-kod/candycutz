<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Booking\Actions\ApproveAppointment;
use App\Domain\Booking\Actions\CancelAppointment;
use App\Domain\Booking\Actions\CreateWalkInAppointment;
use App\Domain\Booking\Actions\ForceApproveAppointment;
use App\Domain\Booking\Actions\GetAppointments;
use App\Domain\Booking\Actions\GetVerifications;
use App\Domain\Booking\Actions\GetVerificationStats;
use App\Domain\Booking\Actions\VerifyAppointment;
use App\Domain\Catalogue\Actions\CreateService;
use App\Domain\Catalogue\Actions\CreateServiceCategory;
use App\Domain\Catalogue\Actions\DeleteService;
use App\Domain\Catalogue\Actions\DeleteServiceCategory;
use App\Domain\Catalogue\Actions\GetServiceCategories;
use App\Domain\Catalogue\Actions\GetServices;
use App\Domain\Catalogue\Actions\UpdateService;
use App\Domain\Catalogue\Actions\UpdateServiceCategory;
use App\Domain\Content\Actions\ApproveTestimonial;
use App\Domain\Content\Actions\CreateBlogPost;
use App\Domain\Content\Actions\CreateGalleryItem;
use App\Domain\Content\Actions\DeleteBlogPost;
use App\Domain\Content\Actions\DeleteGalleryItem;
use App\Domain\Content\Actions\DeleteTestimonial;
use App\Domain\Content\Actions\FeatureTestimonial;
use App\Domain\Content\Actions\GetBlogPosts;
use App\Domain\Content\Actions\GetGallery;
use App\Domain\Content\Actions\GetSettings;
use App\Domain\Content\Actions\GetTestimonials;
use App\Domain\Content\Actions\UpdateBlogPost;
use App\Domain\Content\Actions\UpdateGalleryItem;
use App\Domain\Content\Actions\UpdateSettings;
use App\Domain\Content\Actions\UpdateTestimonial;
use App\Domain\Identity\Actions\CreateBarber;
use App\Domain\Identity\Actions\CreateHoliday;
use App\Domain\Identity\Actions\CreateWorkingHour;
use App\Domain\Identity\Actions\DeleteBarber;
use App\Domain\Identity\Actions\DeleteHoliday;
use App\Domain\Identity\Actions\DeleteWorkingHour;
use App\Domain\Identity\Actions\GetAuditLogs;
use App\Domain\Identity\Actions\GetBarbers;
use App\Domain\Identity\Actions\GetCustomerProfile;
use App\Domain\Identity\Actions\GetCustomers;
use App\Domain\Identity\Actions\GetHolidays;
use App\Domain\Identity\Actions\GetWorkingHours;
use App\Domain\Identity\Actions\UpdateBarber;
use App\Domain\Identity\Actions\UpdateBarberStatus;
use App\Domain\Identity\Actions\UpdateBarberWorkingHours;
use App\Domain\Identity\Actions\UpdateHoliday;
use App\Domain\Identity\Actions\UpdateWorkingHour;
use App\Domain\System\Actions\GetDashboardStats;
use App\Domain\System\Actions\GetReports;
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
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Testimonial;
use App\Models\WorkingHour;
use App\Modules\Customer\Resources\AppointmentResource;
use App\Modules\Customer\Resources\TestimonialResource;
use Illuminate\Http\Request;

class AdminApiController
{
    public function dashboard(GetDashboardStats $action)
    {
        return ApiResponse::success($action->execute(), 'Admin dashboard loaded');
    }

    public function reports(GetReports $action)
    {
        return ApiResponse::success($action->execute(), 'Reports loaded');
    }

    public function settings(GetSettings $action)
    {
        return ApiResponse::success($action->execute(), 'Settings loaded');
    }

    public function updateSettings(UpdateSettingsRequest $request, UpdateSettings $action)
    {
        return ApiResponse::success(
            $action->execute($request->validated('settings', []), $request->file('hero_image')),
            'Settings updated'
        );
    }

    public function appointments(GetAppointments $action)
    {
        return ApiResponse::paginated($action->execute(), 'Appointments loaded');
    }

    public function approveAppointment(Appointment $appointment, ApproveAppointment $action)
    {
        return ApiResponse::success(new AppointmentResource($action->execute($appointment)), 'Appointment approved');
    }

    public function cancelAppointment(Appointment $appointment, CancelAppointment $action)
    {
        return ApiResponse::success(new AppointmentResource($action->execute($appointment)), 'Appointment cancelled');
    }

    public function services(GetServices $action)
    {
        return ApiResponse::success($action->execute(), 'Services loaded');
    }

    public function storeService(StoreServiceRequest $request, CreateService $action)
    {
        return ApiResponse::success($action->execute($request->validated()), 'Service created', 201);
    }

    public function updateService(UpdateServiceRequest $request, Service $service, UpdateService $action)
    {
        return ApiResponse::success($action->execute($service, $request->validated()), 'Service updated');
    }

    public function deleteService(Service $service, DeleteService $action)
    {
        $action->execute($service);

        return ApiResponse::success(null, 'Service deleted');
    }

    public function serviceCategories(GetServiceCategories $action)
    {
        return ApiResponse::success($action->execute(), 'Service categories loaded');
    }

    public function storeServiceCategory(StoreServiceCategoryRequest $request, CreateServiceCategory $action)
    {
        return ApiResponse::success($action->execute($request->validated()), 'Service category created', 201);
    }

    public function updateServiceCategory(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory, UpdateServiceCategory $action)
    {
        return ApiResponse::success($action->execute($serviceCategory, $request->validated()), 'Service category updated');
    }

    public function deleteServiceCategory(ServiceCategory $serviceCategory, DeleteServiceCategory $action)
    {
        $action->execute($serviceCategory);

        return ApiResponse::success(null, 'Service category deleted');
    }

    public function gallery(GetGallery $action)
    {
        return ApiResponse::success($action->execute(), 'Gallery loaded');
    }

    public function storeGallery(StoreGalleryRequest $request, CreateGalleryItem $action)
    {
        return ApiResponse::success($action->execute($request->validated(), $request->file('image')), 'Gallery item created', 201);
    }

    public function updateGallery(UpdateGalleryRequest $request, Gallery $gallery, UpdateGalleryItem $action)
    {
        return ApiResponse::success($action->execute($gallery, $request->validated()), 'Gallery item updated');
    }

    public function deleteGallery(Gallery $gallery, DeleteGalleryItem $action)
    {
        $action->execute($gallery);

        return ApiResponse::success(null, 'Gallery item deleted');
    }

    public function testimonials(GetTestimonials $action)
    {
        return ApiResponse::success($action->execute(), 'Testimonials loaded');
    }

    public function approveTestimonial(Testimonial $testimonial, ApproveTestimonial $action)
    {
        return ApiResponse::success(new TestimonialResource($action->execute($testimonial)), 'Testimonial approved');
    }

    public function featureTestimonial(Testimonial $testimonial, FeatureTestimonial $action)
    {
        return ApiResponse::success(new TestimonialResource($action->execute($testimonial)), 'Testimonial feature toggled');
    }

    public function deleteTestimonial(Testimonial $testimonial, DeleteTestimonial $action)
    {
        $action->execute($testimonial);

        return ApiResponse::success(null, 'Testimonial deleted');
    }

    public function blogPosts(GetBlogPosts $action)
    {
        return ApiResponse::success($action->execute(), 'Blog posts loaded');
    }

    public function storeBlogPost(StoreBlogPostRequest $request, CreateBlogPost $action)
    {
        return ApiResponse::success($action->execute($request->user(), $request->validated()), 'Blog post created', 201);
    }

    public function updateBlogPost(UpdateBlogPostRequest $request, BlogPost $blogPost, UpdateBlogPost $action)
    {
        return ApiResponse::success($action->execute($blogPost, $request->validated()), 'Blog post updated');
    }

    public function deleteBlogPost(BlogPost $blogPost, DeleteBlogPost $action)
    {
        $action->execute($blogPost);

        return ApiResponse::success(null, 'Blog post deleted');
    }

    public function workingHours(GetWorkingHours $action)
    {
        return ApiResponse::success($action->execute(), 'Working hours loaded');
    }

    public function storeWorkingHour(StoreWorkingHourRequest $request, CreateWorkingHour $action)
    {
        return ApiResponse::success($action->execute($request->validated()), 'Working hour created', 201);
    }

    public function updateWorkingHour(UpdateWorkingHourRequest $request, WorkingHour $workingHour, UpdateWorkingHour $action)
    {
        return ApiResponse::success($action->execute($workingHour, $request->validated()), 'Working hour updated');
    }

    public function deleteWorkingHour(WorkingHour $workingHour, DeleteWorkingHour $action)
    {
        $action->execute($workingHour);

        return ApiResponse::success(null, 'Working hour deleted');
    }

    public function holidays(GetHolidays $action)
    {
        return ApiResponse::success($action->execute(), 'Holidays loaded');
    }

    public function storeHoliday(StoreHolidayRequest $request, CreateHoliday $action)
    {
        return ApiResponse::success($action->execute($request->validated()), 'Holiday created', 201);
    }

    public function updateHoliday(UpdateHolidayRequest $request, Holiday $holiday, UpdateHoliday $action)
    {
        return ApiResponse::success($action->execute($holiday, $request->validated()), 'Holiday updated');
    }

    public function deleteHoliday(Holiday $holiday, DeleteHoliday $action)
    {
        $action->execute($holiday);

        return ApiResponse::success(null, 'Holiday deleted');
    }

    public function updateTestimonial(Request $request, Testimonial $testimonial, UpdateTestimonial $action)
    {
        $data = $request->validate([
            'is_approved' => 'sometimes|boolean',
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string',
        ]);

        return ApiResponse::success(new TestimonialResource($action->execute($testimonial, $data)), 'Testimonial updated');
    }

    public function updateBarberWorkingHours(Request $request, int $barberId, UpdateBarberWorkingHours $action)
    {
        $hours = $request->input('hours', $request->all());

        return ApiResponse::success($action->execute($barberId, $hours), 'Working hours updated');
    }

    public function barbers(GetBarbers $action)
    {
        return ApiResponse::success($action->execute(), 'Barbers loaded');
    }

    public function storeBarber(Request $request, CreateBarber $action)
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

    public function updateBarber(Request $request, int $barberId, UpdateBarber $action)
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

    public function updateBarberStatus(Request $request, int $barberId, UpdateBarberStatus $action)
    {
        $status = $request->input('status', 'active');

        return ApiResponse::success($action->execute($barberId, $status), 'Barber status updated');
    }

    public function deleteBarber(int $barberId, DeleteBarber $action)
    {
        $action->execute($barberId);

        return ApiResponse::success(null, 'Barber deleted');
    }

    public function customers(GetCustomers $action)
    {
        return ApiResponse::success($action->execute(), 'Customers loaded');
    }

    public function customerProfile(int $id, GetCustomerProfile $action)
    {
        return ApiResponse::success($action->execute($id), 'Customer profile loaded');
    }

    public function logs(GetAuditLogs $action)
    {
        return ApiResponse::success($action->execute(), 'Logs loaded');
    }

    public function verifications(Request $request, GetVerifications $action)
    {
        return ApiResponse::success($action->execute($request->all()), 'Verifications loaded');
    }

    public function verificationStats(GetVerificationStats $action)
    {
        return ApiResponse::success($action->execute(), 'Verification stats loaded');
    }

    public function verifyAppointment(int $id, VerifyAppointment $action)
    {
        return ApiResponse::success(new AppointmentResource($action->execute($id)), 'Appointment verified');
    }

    public function createWalkIn(Request $request, CreateWalkInAppointment $action)
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

    public function forceApproveAppointment(Appointment $appointment, ForceApproveAppointment $action)
    {
        return ApiResponse::success(new AppointmentResource($action->execute($appointment)), 'Appointment force approved');
    }

    public function analytics(Request $request, GetReports $action)
    {
        return ApiResponse::success($action->execute(), 'Analytics loaded');
    }

    public function testEmail(Request $request)
    {
        $to = $request->input('to');

        return ApiResponse::success(['sent_to' => $to], 'Test email sent successfully');
    }
}
