<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalogue\Actions\CreateServiceCategory;
use App\Domain\Catalogue\Actions\DeleteServiceCategory;
use App\Domain\Catalogue\Actions\UpdateServiceCategory;
use App\Domain\Shared\Actions\SecureImageUpload;
use App\Http\Requests\Api\V1\Admin\StoreServiceCategoryRequest;
use App\Http\Requests\Api\V1\Admin\UpdateServiceCategoryRequest;
use App\Http\Resources\ServiceResource;
use App\Http\Responses\ApiResponse;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceApiController
{
    use AuthorizesRequests;

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

        $data = ServiceResource::collection($services);

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

        return ApiResponse::success(new ServiceResource($service), 'Service details retrieved');
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

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Service::class);

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
            $path = (new SecureImageUpload())->execute($request->file('image'), 'uploads/services');
            $service->update(['image' => '/storage/' . $path]);
        }

        return ApiResponse::success(new ServiceResource($service), 'Service created', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $service = Service::findOrFail($id);
        $this->authorize('update', $service);

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
            $path = (new SecureImageUpload())->execute($request->file('image'), 'uploads/services');
            $service->update(['image' => '/storage/' . $path]);
        }

        return ApiResponse::success(new ServiceResource($service->refresh()), 'Service updated');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $service = Service::findOrFail($id);
        $this->authorize('delete', $service);
        $service->delete();

        return ApiResponse::success(null, 'Service deleted');
    }

    public function storeCategory(StoreServiceCategoryRequest $request, CreateServiceCategory $action)
    {
        $this->authorize('create', ServiceCategory::class);
        return ApiResponse::success($action->execute($request->validated()), 'Service category created', 201);
    }

    public function updateCategory(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory, UpdateServiceCategory $action)
    {
        $this->authorize('update', $serviceCategory);
        return ApiResponse::success($action->execute($serviceCategory, $request->validated()), 'Service category updated');
    }

    public function destroyCategory(ServiceCategory $serviceCategory, DeleteServiceCategory $action)
    {
        $this->authorize('delete', $serviceCategory);
        $action->execute($serviceCategory);

        return ApiResponse::success(null, 'Service category deleted');
    }
}
