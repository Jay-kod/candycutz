<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Responses\ApiResponse;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        if (! $service) {
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
            'slug' => $cat->slug ?? Str::slug($cat->name),
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
                : url('storage/'.ltrim($service->image, '/'));
        }

        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug ?? Str::slug($service->name),
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

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->barber && ! in_array($user->role?->value ?? $user->role, ['admin', 'super_admin'])) {
            return ApiResponse::error('Only barbers or admins can create services.', [], 403, 'FORBIDDEN_ROLE');
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'duration_minutes' => 'nullable|integer',
            'category_id' => 'required|integer|exists:service_categories,id',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        $service = Service::create($validated);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'service_'.$service->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/services'), $filename);
            $service->update(['image' => '/uploads/services/'.$filename]);
        }

        return ApiResponse::success($this->formatService($service), 'Service created', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user->barber && ! in_array($user->role?->value ?? $user->role, ['admin', 'super_admin'])) {
            return ApiResponse::error('Only barbers or admins can update services.', [], 403, 'FORBIDDEN_ROLE');
        }

        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric',
            'duration_minutes' => 'nullable|integer',
            'category_id' => 'sometimes|integer|exists:service_categories,id',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['is_active'])) {
            $validated['is_active'] = filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        $service->update($validated);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'service_'.$service->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/services'), $filename);
            $service->update(['image' => '/uploads/services/'.$filename]);
        }

        return ApiResponse::success($this->formatService($service->refresh()), 'Service updated');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user->barber && ! in_array($user->role?->value ?? $user->role, ['admin', 'super_admin'])) {
            return ApiResponse::error('Only barbers or admins can delete services.', [], 403, 'FORBIDDEN_ROLE');
        }

        $service = Service::findOrFail($id);
        $service->delete();

        return ApiResponse::success(null, 'Service deleted');
    }
}
