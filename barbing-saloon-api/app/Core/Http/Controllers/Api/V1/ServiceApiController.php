<?php

declare(strict_types=1);

namespace App\Core\Http\Controllers\Api\V1;

use App\Core\Http\Response\ApiResponse;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Service::query()
            ->with(['category'])
            ->where('is_active', true);

        if ($request->has('category_id') && is_numeric($request->category_id)) {
            $query->where('category_id', (int) $request->category_id);
        }

        if ($request->has('type')) {
            if ($request->type === 'home_service') {
                $query->where('home_service_allowed', true);
            }
        }

        $services = $query->orderBy('display_order')->orderBy('name')->get();

        $data = $services->map(fn (Service $service) => $this->formatService($service));

        return ApiResponse::success($data, 'Services retrieved successfully');
    }

    public function show(string $idOrSlug): JsonResponse
    {
        $service = is_numeric($idOrSlug)
            ? Service::query()->with('category')->find((int) $idOrSlug)
            : Service::query()->with('category')->where('slug', $idOrSlug)->first();

        if (!$service) {
            return ApiResponse::error("Service '{$idOrSlug}' not found.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        return ApiResponse::success($this->formatService($service), 'Service details retrieved');
    }

    public function categories(): JsonResponse
    {
        $categories = ServiceCategory::query()
            ->withCount(['services' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        $data = $categories->map(fn (ServiceCategory $cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'slug' => $cat->slug ?? \Illuminate\Support\Str::slug($cat->name),
            'icon' => $cat->icon,
            'services_count' => $cat->services_count ?? 0,
        ]);

        return ApiResponse::success($data, 'Service categories retrieved');
    }

    protected function formatService(Service $service): array
    {
        $imageUrl = null;
        if ($service->image) {
            $imageUrl = str_starts_with($service->image, 'http')
                ? $service->image
                : url('storage/' . ltrim($service->image, '/'));
        }

        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug ?? \Illuminate\Support\Str::slug($service->name),
            'description' => $service->description ?? '',
            'price' => (float) $service->price,
            'home_service_price' => (float) ($service->price * 1.25), // Standard 25% home service surcharge if not specified
            'duration_minutes' => (int) ($service->duration_minutes ?? 30),
            'category' => $service->category?->name ?? 'General Grooming',
            'category_id' => $service->category_id,
            'category_name' => $service->category?->name ?? 'General Grooming',
            'image_url' => $imageUrl,
            'is_active' => (bool) $service->is_active,
            'is_home_service_eligible' => (bool) $service->home_service_allowed,
        ];
    }
}
