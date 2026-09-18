<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imageUrl = null;
        if ($this->image_path) {
            $imageUrl = str_starts_with($this->image_path, 'http')
                ? $this->image_path
                : url('storage/'.ltrim($this->image_path, '/'));
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description ?? '',
            'image_url' => $imageUrl,
            'category' => $this->category?->value ?? $this->category,
            'barber' => $this->barber ? [
                'id' => $this->barber->id,
                'name' => $this->barber->user?->name ?? 'Unknown',
                'avatar_url' => $this->barber->user?->avatar ? url('storage/'.ltrim($this->barber->user->avatar, '/')) : null,
            ] : null,
            'is_featured' => (bool) $this->is_featured,
        ];
    }
}
