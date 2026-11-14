<?php

namespace App\Http\Resources;

use App\Domain\Shared\Services\UserMediaUrl;
use App\Domain\Shared\Enums\GalleryCategory;
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
        if ($this->image_path) {
            if (str_starts_with($this->image_path, 'http')) {
                $imageUrl = $this->image_path;
            } else {
                $normalized = ltrim($this->image_path, '/');
                if (str_starts_with($normalized, 'storage/')) {
                    $normalized = substr($normalized, strlen('storage/'));
                }
                $imageUrl = url('storage/'.$normalized);
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description ?? '',
            'image_url' => $imageUrl,
            'image_path' => $this->image_path,
            'category' => $this->category instanceof GalleryCategory ? $this->category->value : $this->category,
            'barber_id' => $this->barber_id,
            'barber_name' => $barberUser->name ?? null,
            'barber' => $barber ? [
                'id' => $barber->id,
                'name' => $barberUser->name ?? 'Unknown',
                'avatar_url' => app(UserMediaUrl::class)->for($barberUser, $barberUser->avatar ?? null),
            ] : null,
            'is_featured' => (bool) $this->is_featured,
        ];
    }
}
