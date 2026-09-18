<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Shared\Actions\SecureImageUpload;
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

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'image' => 'required|image|max:5120',
        ]);

        $path = (new SecureImageUpload)->execute($request->file('image'), 'uploads/gallery');

        $gallery = Gallery::create([
            'barber_id' => $barber->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? null,
            'image_path' => '/storage/'.$path,
        ]);

        return ApiResponse::success(new GalleryResource($gallery), 'Gallery item created', 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = Gallery::where('id', $id)->first();
        if (! $item) {
            return ApiResponse::error("Gallery item #{$id} not found.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        $this->authorize('delete', $item);

        $item->delete();

        return ApiResponse::success(null, 'Gallery item deleted');
    }
}
