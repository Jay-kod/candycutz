<?php

namespace App\Http\Resources;

use App\Domain\Shared\Services\UserMediaUrl;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @mixin Service
 */
class ServiceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $barberAvatar = null;
        if ($this->barber?->user->avatar) {
            $barberAvatar = app(UserMediaUrl::class)->for($this->barber->user, $this->barber->user->avatar);
        }

        $imageUrl = $this->formatImageUrl($this->image);
        $image2Url = $this->formatImageUrl($this->image2);
        $image3Url = $this->formatImageUrl($this->image3);
        $images = array_values(array_filter([$imageUrl, $image2Url, $image3Url]));

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug ?? Str::slug($this->name),
            'description' => $this->description ?? '',
            'price' => (float) $this->price,
            'home_service_price' => (float) ($this->price * 1.25),
            'duration_minutes' => (int) ($this->duration_minutes ?? 30),
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug ?? Str::slug($this->category->name),
            ] : [
                'id' => $this->category_id ?? 0,
                'name' => 'General Grooming',
                'slug' => 'general-grooming',
            ],
            'category_id' => $this->category_id,
            'category_name' => $this->category->name ?? 'General Grooming',
            'image' => $imageUrl,
            'image_url' => $imageUrl,
            'thumbnail_url' => $this->formatThumbnailUrl($this->image) ?? $imageUrl,
            'is_optimizing' => $this->image && str_contains((string) $this->image, 'uploads/') && ! str_ends_with(strtolower((string) $this->image), '.webp'),
            'image2' => $image2Url,
            'image2_url' => $image2Url,
            'image3' => $image3Url,
            'image3_url' => $image3Url,
            'images' => $images,
            'is_active' => (bool) $this->is_active,
            'approval_status' => $this->approval_status ?? 'approved',
            'barber_id' => $this->barber_id,
            'barber' => $this->barber ? [
                'id' => $this->barber->id,
                'name' => $this->barber->user->name ?? 'Barber',
                'avatar_url' => $barberAvatar,
            ] : null,
            'is_home_service_eligible' => (bool) $this->home_service_allowed,
        ];
    }

    private function formatImageUrl(?string $image): ?string
    {
        if (! $image) {
            return null;
        }

        if (str_starts_with($image, 'http') || str_starts_with($image, 'data:')) {
            return $image;
        }

        $normalized = ltrim($image, '/');
        if (str_starts_with($normalized, 'storage/')) {
            return url($normalized);
        } elseif (str_starts_with($normalized, 'images/')) {
            return url($normalized);
        }

        return url('storage/'.$normalized);
    }

    private function formatThumbnailUrl(?string $image): ?string
    {
        if (! $image || ! str_ends_with(strtolower($image), '.webp') || str_starts_with($image, 'http') || str_starts_with($image, 'data:')) {
            return null;
        }

        $normalized = ltrim($image, '/');
        if (str_starts_with($normalized, 'images/')) {
            return url($normalized);
        }

        $rel = str_starts_with($normalized, 'storage/') ? $normalized : 'storage/'.$normalized;
        if (! str_contains($rel, 'uploads/')) {
            return url($rel);
        }

        $base = basename($rel);
        $thumbBase = str_starts_with($base, 'thumb_') ? $base : 'thumb_'.$base;

        return url(dirname($rel).'/'.$thumbBase);
    }
}
