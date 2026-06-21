<?php

namespace App\Modules\Landing\Controllers;

use App\Core\Http\Response\ApiResponse;
use App\Modules\Landing\Services\PublicService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicController
{
    public function __construct(protected PublicService $publicService)
    {
    }

    public function settings()
    {
        return ApiResponse::success($this->publicService->settings(), 'Public settings loaded');
    }

    public function services()
    {
        return ApiResponse::success($this->publicService->services(), 'Services loaded');
    }

    public function service(string $slug)
    {
        $service = $this->publicService->serviceBySlug($slug);

        if (! $service) {
            return ApiResponse::error('Forbidden', [], 403);
        }

        return ApiResponse::success($service, 'Service loaded');
    }

    public function serviceCategories()
    {
        return ApiResponse::success($this->publicService->serviceCategories(), 'Service categories loaded');
    }

    public function barbers()
    {
        return ApiResponse::success($this->publicService->barbers(), 'Barbers loaded');
    }

    public function barber(int $id)
    {
        $barber = $this->publicService->barberById($id);

        if (! $barber) {
            return ApiResponse::error('Forbidden', [], 403);
        }

        return ApiResponse::success($barber, 'Barber loaded');
    }

    public function gallery(Request $request)
    {
        return ApiResponse::success($this->publicService->gallery($request->string('category')->toString() ?: null), 'Gallery loaded');
    }

    public function testimonials()
    {
        return ApiResponse::success($this->publicService->testimonials(), 'Testimonials loaded');
    }

    public function blog()
    {
        return ApiResponse::success($this->publicService->blogPosts(), 'Blog loaded');
    }

    public function blogPost(string $slug)
    {
        $post = $this->publicService->blogPostBySlug($slug);

        if (! $post) {
            return ApiResponse::error('Forbidden', [], 403);
        }

        return ApiResponse::success($post, 'Blog post loaded');
    }

    public function workingHours()
    {
        return ApiResponse::success($this->publicService->workingHours(), 'Working hours loaded');
    }

    public function availableSlots(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'barber_id' => ['required', 'integer', 'exists:barbers,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
        ]);

        $slots = $this->publicService->availableSlots(
            Carbon::parse($validated['date']),
            (int) $validated['barber_id'],
            (int) $validated['service_id']
        );

        return ApiResponse::success($slots, 'Available slots loaded');
    }

    public function contact(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'min:10'],
        ]);

        return ApiResponse::success($this->publicService->contact($data), 'Message received');
    }
}