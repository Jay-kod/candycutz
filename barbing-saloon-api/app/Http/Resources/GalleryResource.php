<?php

namespace App\Http\Resources;

use App\Domain\Shared\Services\UserMediaUrl;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Gallery
 */
class GalleryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
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
            'category' => $this->category?->value ?? $this->category,
            'barber_id' => $this->barber_id,
            'barber_name' => $this->barber?->user?->name ?? $this->barber?->name ?? null,
            'barber' => $this->barber ? [
                'id' => $this->barber->id,
                'name' => $this->barber->user?->name ?? 'Unknown',
                'avatar_url' => app(UserMediaUrl::class)->for($this->barber->user, $this->barber->user?->avatar),
            ] : null,
            'is_featured' => (bool) $this->is_featured,
        ];
    }
}
