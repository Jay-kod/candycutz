<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalogue\Services\CatalogueService;
use App\Domain\Content\Services\ContentService;
use App\Domain\Content\Services\SettingsService;
use App\Http\Responses\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicApiController
{
    public function settings(SettingsService $settingsService): JsonResponse
    {
        return ApiResponse::success($settingsService->settings(), 'Public settings loaded');
    }

    public function services(CatalogueService $catalogueService): JsonResponse
    {
        return ApiResponse::success($catalogueService->services(), 'Services loaded');
    }

    public function service(string $slug, CatalogueService $catalogueService): JsonResponse
    {
        $service = $catalogueService->serviceBySlug($slug);

        if (! $service) {
            return ApiResponse::error('Forbidden', [], 403);
        }

        return ApiResponse::success($service, 'Service loaded');
    }

    public function serviceCategories(CatalogueService $catalogueService): JsonResponse
    {
        return ApiResponse::success($catalogueService->serviceCategories(), 'Service categories loaded');
    }

    public function barbers(CatalogueService $catalogueService): JsonResponse
    {
        return ApiResponse::success($catalogueService->barbers(), 'Barbers loaded');
    }

    public function barber(int $id, CatalogueService $catalogueService): JsonResponse
    {
        $barber = $catalogueService->barberById($id);

        if (! $barber) {
            return ApiResponse::error('Forbidden', [], 403);
        }

        return ApiResponse::success($barber, 'Barber loaded');
    }

    public function gallery(Request $request, ContentService $contentService): JsonResponse
    {
        return ApiResponse::success($contentService->gallery($request->string('category')->toString() ?: null), 'Gallery loaded');
    }

    public function testimonials(ContentService $contentService): JsonResponse
    {
        return ApiResponse::success($contentService->testimonials(), 'Testimonials loaded');
    }

    public function blog(ContentService $contentService): JsonResponse
    {
        return ApiResponse::success($contentService->blogPosts(), 'Blog loaded');
    }

    public function blogPost(string $slug, ContentService $contentService): JsonResponse
    {
        $post = $contentService->blogPostBySlug($slug);

        if (! $post) {
            return ApiResponse::error('Forbidden', [], 403);
        }

        return ApiResponse::success($post, 'Blog post loaded');
    }

    public function workingHours(CatalogueService $catalogueService): JsonResponse
    {
        return ApiResponse::success($catalogueService->workingHours(), 'Working hours loaded');
    }

    public function availableSlots(Request $request, CatalogueService $catalogueService): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'barber_id' => ['required', 'integer', 'exists:barbers,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
        ]);

        $slots = $catalogueService->availableSlots(
            Carbon::parse($validated['date']),
            (int) $validated['barber_id'],
            (int) $validated['service_id']
        );

        return ApiResponse::success($slots, 'Available slots loaded');
    }

    public function contact(Request $request, ContentService $contentService): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'min:10'],
        ]);

        return ApiResponse::success($contentService->contact($data), 'Message received');
    }
}
