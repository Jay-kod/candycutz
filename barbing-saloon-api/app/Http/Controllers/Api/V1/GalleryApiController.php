<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Shared\Actions\SecureImageUpload;
use App\Domain\Shared\Services\CatalogueCache;
use App\Http\Resources\GalleryResource;
use App\Http\Responses\ApiResponse;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryApiController
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $category = $request->has('category') && $request->category !== 'all' ? (string) $request->category : 'all';
        $barberId = $request->has('barber_id') && is_numeric($request->barber_id) ? (int) $request->barber_id : 0;
        $featured = $request->boolean('featured') ? 1 : 0;
        $perPage = $request->filled('per_page') ? min(100, max(1, $request->integer('per_page'))) : 0;
        $page = $request->integer('page', 1);

        $cacheKey = "list:cat={$category}:barber={$barberId}:feat={$featured}:pp={$perPage}:p={$page}";

        $payload = CatalogueCache::remember('gallery', $cacheKey, function () use ($category, $barberId, $featured, $perPage): array {
            $query = Gallery::query()
                ->with(['barber.user'])
                ->whereNull('deleted_at');

            if ($category !== 'all') {
                $query->where('category', $category);
            }

            if ($barberId > 0) {
                $query->where('barber_id', $barberId);
            }

            if ($featured === 1) {
                $query->where('is_featured', true);
            }

            $ordered = $query->orderBy('display_order')->orderByDesc('created_at');

            if ($perPage > 0) {
                $paginator = $ordered->paginate($perPage);

                return [
                    'paginated' => true,
                    'items' => GalleryResource::collection($paginator->items())->resolve(),
                    'meta' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                    ],
                ];
            }

            return [
                'paginated' => false,
                'items' => GalleryResource::collection($ordered->get())->resolve(),
            ];
        });

        if (! empty($payload['paginated'])) {
            return ApiResponse::success([
                'items' => $payload['items'],
                'meta' => $payload['meta'],
            ], 'Gallery retrieved successfully');
        }

        return ApiResponse::success($payload['items'], 'Gallery retrieved successfully');
    }

    public function show(int $id): JsonResponse
    {
        $item = Gallery::query()->with(['barber.user'])->find($id);

        if (! $item) {
            return ApiResponse::error("Gallery item #{$id} not found.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        return ApiResponse::success(new GalleryResource($item), 'Gallery item retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Gallery::class);

        $user = $request->user();
        $barber = $user->barber;
        $barberId = $barber->id ?? ($request->has('barber_id') ? (int) $request->barber_id : null);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'image' => 'required|image|max:5120',
        ]);

        $category = $this->resolveCategorySlug($validated['category'] ?? null);

        $path = (new SecureImageUpload)->storeFast($request->file('image'), 'uploads/gallery');

        $gallery = Gallery::create([
            'barber_id' => $barberId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $category,
            'image_path' => '/storage/'.$path,
        ]);

        (new SecureImageUpload)->dispatchOptimization(
            rawPath: $path,
            disk: 'public',
            modelClass: Gallery::class,
            modelKey: $gallery->id,
            attribute: 'image_path',
            prefixStorage: true,
            actorUserId: $user->id,
            cacheEntity: 'gallery',
        );

        CatalogueCache::bust('gallery');
        CatalogueCache::bust('gallery_categories');

        return ApiResponse::success(new GalleryResource($gallery->refresh()->load('barber.user')), 'Gallery item created', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $item = Gallery::where('id', $id)->first();
        if (! $item) {
            return ApiResponse::error("Gallery item #{$id} not found.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        $this->authorize('update', $item);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:5120',
            'image_url' => 'nullable|string',
            'barber_id' => 'nullable|integer|exists:barbers,id',
        ]);

        if (isset($validated['category'])) {
            $item->category = $this->resolveCategorySlug((string) $validated['category']);
        }

        if (isset($validated['title'])) {
            $item->title = $validated['title'];
        }

        if (array_key_exists('description', $validated)) {
            $item->description = $validated['description'];
        }

        $userRole = $request->user()->role->value ?? $request->user()->role;
        if (in_array($userRole, ['admin', 'super_admin'], true) && array_key_exists('barber_id', $validated)) {
            $item->barber_id = $validated['barber_id'] ? (int) $validated['barber_id'] : null;
        }

        $uploadedPath = null;
        if ($request->hasFile('image')) {
            $uploadedPath = (new SecureImageUpload)->storeFast($request->file('image'), 'uploads/gallery');
            $item->image_path = '/storage/'.$uploadedPath;
        } elseif (! empty($validated['image_url']) && empty($item->image_path)) {
            $item->image_path = $validated['image_url'];
        }

        $item->save();

        if ($uploadedPath !== null) {
            (new SecureImageUpload)->dispatchOptimization(
                rawPath: $uploadedPath,
                disk: 'public',
                modelClass: Gallery::class,
                modelKey: $item->id,
                attribute: 'image_path',
                prefixStorage: true,
                actorUserId: $request->user()?->id,
                cacheEntity: 'gallery',
            );
        }

        CatalogueCache::bust('gallery');
        CatalogueCache::bust('gallery_categories');

        return ApiResponse::success(new GalleryResource($item->refresh()->load('barber.user')), 'Gallery item updated');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = Gallery::where('id', $id)->first();
        if (! $item) {
            return ApiResponse::error("Gallery item #{$id} not found.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        $this->authorize('delete', $item);

        $item->delete();
        CatalogueCache::bust('gallery');
        CatalogueCache::bust('gallery_categories');

        return ApiResponse::success(null, 'Gallery item deleted');
    }

    private function resolveCategorySlug(?string $raw): string
    {
        $normalized = strtolower(trim((string) $raw));
        if ($normalized === '') {
            return 'haircut';
        }

        $matchedSlug = GalleryCategory::query()
            ->where('slug', $normalized)
            ->orWhereRaw('LOWER(name) = ?', [$normalized])
            ->value('slug');

        return $matchedSlug ?: $normalized;
    }
}
