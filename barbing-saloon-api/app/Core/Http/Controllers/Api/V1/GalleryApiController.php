<?php

declare(strict_types=1);

namespace App\Core\Http\Controllers\Api\V1;

use App\Core\Http\Response\ApiResponse;
use App\Models\Gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryApiController
{
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

        $data = $gallery->map(fn (Gallery $item) => $this->formatGallery($item));

        return ApiResponse::success($data, 'Gallery retrieved successfully');
    }

    public function show(int $id): JsonResponse
    {
        $item = Gallery::query()->with(['barber.user'])->find($id);

        if (!$item) {
            return ApiResponse::error("Gallery item #{$id} not found.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        return ApiResponse::success($this->formatGallery($item), 'Gallery item retrieved');
    }

    protected function formatGallery(Gallery $item): array
    {
        $imageUrl = null;
        if ($item->image_path) {
            $imageUrl = str_starts_with($item->image_path, 'http')
                ? $item->image_path
                : url('storage/' . ltrim($item->image_path, '/'));
        }

        return [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description ?? '',
            'image_url' => $imageUrl,
            'category' => $item->category?->value ?? $item->category,
            'barber' => $item->barber ? [
                'id' => $item->barber->id,
                'name' => $item->barber->user?->name ?? 'Unknown',
                'avatar_url' => $item->barber->user?->avatar ? url('storage/' . ltrim($item->barber->user->avatar, '/')) : null,
            ] : null,
            'is_featured' => (bool) $item->is_featured,
        ];
    }
}