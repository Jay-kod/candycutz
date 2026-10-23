<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Shared\Actions\SecureImageUpload;
use App\Domain\Shared\Enums\GalleryCategory;
use App\Domain\Shared\Services\CatalogueCache;
use App\Http\Resources\GalleryResource;
use App\Http\Responses\ApiResponse;
use App\Models\Gallery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryApiController
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $query = Gallery::query()
            ->with(['barber.user'])
            ->where('deleted_at', null);

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->has('barber_id') && is_numeric($request->barber_id)) {
            $query->where('barber_id', (int) $request->barber_id);
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $gallery = $query->orderBy('display_order')->orderByDesc('created_at')->get();

        $data = GalleryResource::collection($gallery);

        return ApiResponse::success($data, 'Gallery retrieved successfully');
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
        $barberId = $barber?->id ?? ($request->has('barber_id') ? (int) $request->barber_id : null);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'image' => 'required|image|max:5120',
        ]);

        $category = $validated['category'] ?? 'haircut';
        $validCategories = ['haircut', 'beard', 'combo', 'before_after', 'shop'];
        if (! in_array($category, $validCategories)) {
            $category = 'haircut';
        }

        $path = (new SecureImageUpload)->execute($request->file('image'), 'uploads/gallery');

        $gallery = Gallery::create([
            'barber_id' => $barberId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $category,
            'image_path' => '/storage/'.$path,
        ]);

        CatalogueCache::bust('gallery');

        return ApiResponse::success(new GalleryResource($gallery->load('barber.user')), 'Gallery item created', 201);
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
            'category' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'image_url' => 'nullable|string',
            'barber_id' => 'nullable|integer|exists:barbers,id',
        ]);

        if (isset($validated['category'])) {
            $category = $validated['category'];
            $validCategories = ['haircut', 'beard', 'combo', 'before_after', 'shop'];
            if (! in_array($category, $validCategories, true)) {
                $category = 'haircut';
            }
            $item->category = GalleryCategory::tryFrom($category) ?? GalleryCategory::haircut;
        }

        if (isset($validated['title'])) {
            $item->title = $validated['title'];
        }

        if (array_key_exists('description', $validated)) {
            $item->description = $validated['description'];
        }

        $userRole = $request->user()->role?->value ?? $request->user()->role;
        if (in_array($userRole, ['admin', 'super_admin'], true) && array_key_exists('barber_id', $validated)) {
            $item->barber_id = $validated['barber_id'] ? (int) $validated['barber_id'] : null;
        }

        if ($request->hasFile('image')) {
            $path = (new SecureImageUpload)->execute($request->file('image'), 'uploads/gallery');
            $item->image_path = '/storage/'.$path;
        } elseif (! empty($validated['image_url']) && empty($item->image_path)) {
            $item->image_path = $validated['image_url'];
        }

        $item->save();

        CatalogueCache::bust('gallery');

        return ApiResponse::success(new GalleryResource($item->load('barber.user')), 'Gallery item updated');
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

        return ApiResponse::success(null, 'Gallery item deleted');
    }
}
