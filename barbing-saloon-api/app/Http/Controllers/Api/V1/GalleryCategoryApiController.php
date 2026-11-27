<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Content\Actions\CreateGalleryCategory;
use App\Domain\Content\Actions\DeleteGalleryCategory;
use App\Domain\Content\Actions\UpdateGalleryCategory;
use App\Domain\Shared\Services\CatalogueCache;
use App\Http\Requests\Api\V1\Admin\StoreGalleryCategoryRequest;
use App\Http\Requests\Api\V1\Admin\UpdateGalleryCategoryRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GalleryCategoryApiController
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $includeInactive = $request->boolean('include_inactive', false);
        $user = $request->user('sanctum');
        $isAdmin = $user && in_array($user->role->value ?? $user->role, ['admin', 'super_admin'], true);

        $cacheKey = 'gallery_categories:'.($includeInactive && $isAdmin ? 'all' : 'active');

        $categories = CatalogueCache::remember('gallery_categories', $cacheKey, function () use ($includeInactive, $isAdmin) {
            $query = GalleryCategory::query();

            if (! $includeInactive || ! $isAdmin) {
                $query->where('is_active', true);
            }

            $list = $query->orderBy('display_order')->orderBy('name')->get();

            // Count gallery items per category slug (case-insensitive)
            $counts = [];
            $rows = Gallery::query()
                ->whereNull('deleted_at')
                ->select('category', DB::raw('count(*) as aggregate'))
                ->groupBy('category')
                ->get();

            foreach ($rows as $row) {
                $key = strtolower(trim((string) $row->category));
                $counts[$key] = ($counts[$key] ?? 0) + (int) $row->aggregate;
            }

            return $list->map(function (GalleryCategory $cat) use ($counts) {
                $item = $cat->toArray();
                $slugKey = strtolower(trim((string) $cat->slug));
                $nameKey = strtolower(trim((string) $cat->name));
                $item['items_count'] = $counts[$slugKey] ?? $counts[$nameKey] ?? 0;

                return $item;
            })->all();
        });

        return ApiResponse::success($categories, 'Gallery categories retrieved successfully');
    }

    public function store(StoreGalleryCategoryRequest $request, CreateGalleryCategory $action): JsonResponse
    {
        $this->authorize('create', GalleryCategory::class);

        $category = $action->execute($request->validated());

        CatalogueCache::bust('gallery');
        CatalogueCache::bust('gallery_categories');

        return ApiResponse::success($category, 'Gallery category created successfully', 201);
    }

    public function update(UpdateGalleryCategoryRequest $request, int $id, UpdateGalleryCategory $action): JsonResponse
    {
        $category = GalleryCategory::find($id);
        if (! $category) {
            return ApiResponse::error("Gallery category #{$id} not found.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        $this->authorize('update', $category);

        $updated = $action->execute($category, $request->validated());

        CatalogueCache::bust('gallery');
        CatalogueCache::bust('gallery_categories');

        return ApiResponse::success($updated, 'Gallery category updated successfully');
    }

    public function destroy(int $id, DeleteGalleryCategory $action): JsonResponse
    {
        $category = GalleryCategory::find($id);
        if (! $category) {
            return ApiResponse::error("Gallery category #{$id} not found.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        $this->authorize('delete', $category);

        $action->execute($category);

        CatalogueCache::bust('gallery');
        CatalogueCache::bust('gallery_categories');

        return ApiResponse::success(null, 'Gallery category deleted successfully');
    }
}
