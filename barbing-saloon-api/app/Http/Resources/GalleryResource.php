<?php

namespace App\Http\Resources;

use App\Domain\Shared\Enums\GalleryCategory;
use App\Domain\Shared\Services\UserMediaUrl;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Gallery
 */
class GalleryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $barber = $this->barber;
        $barberUser = $barber?->user;
        $imageUrl = null;
        $thumbnailUrl = null;
        $isOptimizing = false;

        if ($this->image_path) {
            if (str_starts_with($this->image_path, 'http') || str_starts_with($this->image_path, 'data:')) {
                $imageUrl = $this->image_path;
                $thumbnailUrl = $this->image_path;
            } else {
                $normalized = ltrim($this->image_path, '/');
                if (str_starts_with($normalized, 'images/')) {
                    $imageUrl = url($normalized);
                    $thumbnailUrl = $imageUrl;
                } else {
                    $storageRelative = str_starts_with($normalized, 'storage/') ? $normalized : 'storage/'.$normalized;
                    $imageUrl = url($storageRelative);

                    if (str_ends_with(strtolower($storageRelative), '.webp') && str_contains($storageRelative, 'uploads/')) {
                        $dir = dirname($storageRelative);
                        $base = basename($storageRelative);
                        $thumbRelative = $dir.'/'.(str_starts_with($base, 'thumb_') ? $base : 'thumb_'.$base);
                        $thumbnailUrl = url($thumbRelative);
                    } else {
                        $thumbnailUrl = $imageUrl;
                        $isOptimizing = ! str_ends_with(strtolower($storageRelative), '.webp') && str_contains($storageRelative, 'uploads/');
                    }
                }
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description ?? '',
            'image_url' => $imageUrl,
            'thumbnail_url' => $thumbnailUrl,
            'is_optimizing' => $isOptimizing,
            'image_path' => $this->image_path,
            'category' => $this->category instanceof GalleryCategory ? $this->category->value : $this->category,
            'barber_id' => $this->barber_id,
            'barber_name' => $barberUser->name ?? null,
            'barber' => $barber ? [
                'id' => $barber->id,
                'name' => $barberUser->name ?? 'Unknown',
                'avatar_url' => app(UserMediaUrl::class)->for($barberUser, $barberUser->avatar ?? null),
                'avatar_thumb_url' => app(UserMediaUrl::class)->thumbnailFor($barberUser, $barberUser->avatar ?? null),
            ] : null,
            'is_featured' => (bool) $this->is_featured,
        ];
    }
}
