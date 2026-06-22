<?php

namespace App\Modules\Barber\Controllers;

use App\Core\Http\Response\ApiResponse;
use App\Models\Appointment;
use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\Service;
use App\Modules\Barber\Requests\UpdateBarberProfileRequest;
use App\Modules\Barber\Resources\BarberAppointmentResource;
use App\Modules\Barber\Resources\BarberProfileResource;
use App\Modules\Barber\Services\BarberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BarberController
{
    public function __construct(protected BarberService $barberService)
    {
    }

    public function dashboard(Request $request)
    {
        return ApiResponse::success($this->barberService->dashboard($request->user()), 'Barber dashboard loaded');
    }

    public function schedule(Request $request)
    {
        return ApiResponse::success($this->barberService->schedule($request->user()), 'Schedule loaded');
    }

    public function appointments(Request $request)
    {
        return ApiResponse::paginated($this->barberService->appointments($request->user()), 'Appointments loaded');
    }

    public function complete(Request $request, Appointment $appointment)
    {
        Gate::authorize('complete', $appointment);

        return ApiResponse::success(new BarberAppointmentResource($this->barberService->complete($appointment)), 'Appointment completed');
    }

    public function noShow(Request $request, Appointment $appointment)
    {
        Gate::authorize('markNoShow', $appointment);

        return ApiResponse::success(new BarberAppointmentResource($this->barberService->noShow($appointment)), 'Appointment marked as no-show');
    }

    public function profile(Request $request)
    {
        return ApiResponse::success(new BarberProfileResource($this->barberService->profile($request->user())), 'Barber profile loaded');
    }

    public function updateProfile(UpdateBarberProfileRequest $request)
    {
        return ApiResponse::success(new BarberProfileResource($this->barberService->updateProfile($request->user(), $request->validated())), 'Barber profile updated');
    }

    public function services(Request $request)
    {
        return ApiResponse::success($this->barberService->services($request->user()), 'Services loaded');
    }

    public function storeService(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'duration_minutes' => 'nullable|integer',
            'category_id' => 'required|integer|exists:service_categories,id',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ]);
        return ApiResponse::success($this->barberService->storeService($request->user(), $data), 'Service created', 201);
    }

    public function updateService(Request $request, Service $service)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric',
            'duration_minutes' => 'nullable|integer',
            'category_id' => 'sometimes|integer|exists:service_categories,id',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ]);
        return ApiResponse::success($this->barberService->updateService($request->user(), $service, $data), 'Service updated');
    }

    public function deleteService(Request $request, Service $service)
    {
        $this->barberService->deleteService($request->user(), $service);
        return ApiResponse::success(null, 'Service deleted');
    }

    public function gallery(Request $request)
    {
        return ApiResponse::success($this->barberService->gallery($request->user()), 'Gallery loaded');
    }

    public function storeGallery(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'image' => 'required|image|max:2048',
        ]);
        return ApiResponse::success($this->barberService->storeGallery($request->user(), $data, $request->file('image')), 'Gallery item created', 201);
    }

    public function deleteGallery(Request $request, Gallery $gallery)
    {
        $this->barberService->deleteGallery($request->user(), $gallery);
        return ApiResponse::success(null, 'Gallery item deleted');
    }

    public function blogPosts(Request $request)
    {
        return ApiResponse::success($this->barberService->blogPosts($request->user()), 'Blog posts loaded');
    }

    public function storeBlogPost(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
        ]);
        return ApiResponse::success($this->barberService->storeBlogPost($request->user(), $data), 'Blog post created', 201);
    }

    public function updateBlogPost(Request $request, BlogPost $blogPost)
    {
        $data = $request->validate([
            'title' => 'sometimes|string',
            'excerpt' => 'nullable|string',
            'content' => 'sometimes|string',
            'featured_image' => 'nullable|image|max:2048',
            'is_published' => 'nullable|boolean',
        ]);
        if (isset($data['is_published'])) {
            $data['status'] = $data['is_published'] ? 'published' : 'draft';
            unset($data['is_published']);
        }
        return ApiResponse::success($this->barberService->updateBlogPost($request->user(), $blogPost, $data), 'Blog post updated');
    }

    public function deleteBlogPost(Request $request, BlogPost $blogPost)
    {
        $this->barberService->deleteBlogPost($request->user(), $blogPost);
        return ApiResponse::success(null, 'Blog post deleted');
    }
}