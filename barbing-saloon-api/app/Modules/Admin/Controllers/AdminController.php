<?php

namespace App\Modules\Admin\Controllers;

use App\Core\Http\Response\ApiResponse;
use App\Models\Appointment;
use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Testimonial;
use App\Models\WorkingHour;
use App\Modules\Admin\Requests\StoreBlogPostRequest;
use App\Modules\Admin\Requests\StoreGalleryRequest;
use App\Modules\Admin\Requests\StoreHolidayRequest;
use App\Modules\Admin\Requests\StoreServiceCategoryRequest;
use App\Modules\Admin\Requests\StoreServiceRequest;
use App\Modules\Admin\Requests\StoreWorkingHourRequest;
use App\Modules\Admin\Requests\UpdateBlogPostRequest;
use App\Modules\Admin\Requests\UpdateGalleryRequest;
use App\Modules\Admin\Requests\UpdateHolidayRequest;
use App\Modules\Admin\Requests\UpdateServiceCategoryRequest;
use App\Modules\Admin\Requests\UpdateServiceRequest;
use App\Modules\Admin\Requests\UpdateSettingsRequest;
use App\Modules\Admin\Requests\UpdateWorkingHourRequest;
use App\Modules\Admin\Services\AdminService;
use App\Modules\Customer\Resources\AppointmentResource;
use App\Modules\Customer\Resources\TestimonialResource;
use Illuminate\Http\Request;

class AdminController
{
    public function __construct(protected AdminService $adminService)
    {
    }

    public function dashboard()
    {
        return ApiResponse::success($this->adminService->dashboard(), 'Admin dashboard loaded');
    }

    public function reports()
    {
        return ApiResponse::success($this->adminService->reports(), 'Reports loaded');
    }

    public function settings()
    {
        return ApiResponse::success($this->adminService->settings(), 'Settings loaded');
    }

    public function updateSettings(UpdateSettingsRequest $request)
    {
        return ApiResponse::success(
            $this->adminService->updateSettings($request->validated('settings', []), $request->file('hero_image')),
            'Settings updated'
        );
    }

    public function appointments()
    {
        return ApiResponse::paginated($this->adminService->appointments(), 'Appointments loaded');
    }

    public function approveAppointment(Appointment $appointment)
    {
        return ApiResponse::success(new AppointmentResource($this->adminService->approveAppointment($appointment)), 'Appointment approved');
    }

    public function cancelAppointment(Appointment $appointment)
    {
        return ApiResponse::success(new AppointmentResource($this->adminService->cancelAppointment($appointment)), 'Appointment cancelled');
    }

    public function services()
    {
        return ApiResponse::success($this->adminService->services(), 'Services loaded');
    }

    public function storeService(StoreServiceRequest $request)
    {
        return ApiResponse::success($this->adminService->storeService($request->validated()), 'Service created', 201);
    }

    public function updateService(UpdateServiceRequest $request, Service $service)
    {
        return ApiResponse::success($this->adminService->updateService($service, $request->validated()), 'Service updated');
    }

    public function deleteService(Service $service)
    {
        $this->adminService->deleteService($service);

        return ApiResponse::success(null, 'Service deleted');
    }

    public function serviceCategories()
    {
        return ApiResponse::success($this->adminService->serviceCategories(), 'Service categories loaded');
    }

    public function storeServiceCategory(StoreServiceCategoryRequest $request)
    {
        return ApiResponse::success($this->adminService->storeServiceCategory($request->validated()), 'Service category created', 201);
    }

    public function updateServiceCategory(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory)
    {
        return ApiResponse::success($this->adminService->updateServiceCategory($serviceCategory, $request->validated()), 'Service category updated');
    }

    public function deleteServiceCategory(ServiceCategory $serviceCategory)
    {
        $this->adminService->deleteServiceCategory($serviceCategory);

        return ApiResponse::success(null, 'Service category deleted');
    }

    public function gallery()
    {
        return ApiResponse::success($this->adminService->gallery(), 'Gallery loaded');
    }

    public function storeGallery(StoreGalleryRequest $request)
    {
        return ApiResponse::success($this->adminService->storeGallery($request->validated(), $request->file('image')), 'Gallery item created', 201);
    }

    public function updateGallery(UpdateGalleryRequest $request, Gallery $gallery)
    {
        return ApiResponse::success($this->adminService->updateGallery($gallery, $request->validated()), 'Gallery item updated');
    }

    public function deleteGallery(Gallery $gallery)
    {
        $this->adminService->deleteGallery($gallery);

        return ApiResponse::success(null, 'Gallery item deleted');
    }

    public function testimonials()
    {
        return ApiResponse::success($this->adminService->testimonials(), 'Testimonials loaded');
    }

    public function approveTestimonial(Testimonial $testimonial)
    {
        return ApiResponse::success(new TestimonialResource($this->adminService->approveTestimonial($testimonial)), 'Testimonial approved');
    }

    public function featureTestimonial(Testimonial $testimonial)
    {
        return ApiResponse::success(new TestimonialResource($this->adminService->featureTestimonial($testimonial)), 'Testimonial feature toggled');
    }

    public function deleteTestimonial(Testimonial $testimonial)
    {
        $this->adminService->deleteTestimonial($testimonial);

        return ApiResponse::success(null, 'Testimonial deleted');
    }

    public function blogPosts()
    {
        return ApiResponse::success($this->adminService->blogPosts(), 'Blog posts loaded');
    }

    public function storeBlogPost(StoreBlogPostRequest $request)
    {
        return ApiResponse::success($this->adminService->storeBlogPost($request->user(), $request->validated()), 'Blog post created', 201);
    }

    public function updateBlogPost(UpdateBlogPostRequest $request, BlogPost $blogPost)
    {
        return ApiResponse::success($this->adminService->updateBlogPost($blogPost, $request->validated()), 'Blog post updated');
    }

    public function deleteBlogPost(BlogPost $blogPost)
    {
        $this->adminService->deleteBlogPost($blogPost);

        return ApiResponse::success(null, 'Blog post deleted');
    }

    public function workingHours()
    {
        return ApiResponse::success($this->adminService->workingHours(), 'Working hours loaded');
    }

    public function storeWorkingHour(StoreWorkingHourRequest $request)
    {
        return ApiResponse::success($this->adminService->storeWorkingHour($request->validated()), 'Working hour created', 201);
    }

    public function updateWorkingHour(UpdateWorkingHourRequest $request, WorkingHour $workingHour)
    {
        return ApiResponse::success($this->adminService->updateWorkingHour($workingHour, $request->validated()), 'Working hour updated');
    }

    public function deleteWorkingHour(WorkingHour $workingHour)
    {
        $this->adminService->deleteWorkingHour($workingHour);

        return ApiResponse::success(null, 'Working hour deleted');
    }

    public function holidays()
    {
        return ApiResponse::success($this->adminService->holidays(), 'Holidays loaded');
    }

    public function storeHoliday(StoreHolidayRequest $request)
    {
        return ApiResponse::success($this->adminService->storeHoliday($request->validated()), 'Holiday created', 201);
    }

    public function updateHoliday(UpdateHolidayRequest $request, Holiday $holiday)
    {
        return ApiResponse::success($this->adminService->updateHoliday($holiday, $request->validated()), 'Holiday updated');
    }

    public function deleteHoliday(Holiday $holiday)
    {
        $this->adminService->deleteHoliday($holiday);

        return ApiResponse::success(null, 'Holiday deleted');
    }
}