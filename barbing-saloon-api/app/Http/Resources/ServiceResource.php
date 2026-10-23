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
    public function toArray(Request $request): array
    {
        $barberAvatar = null;
        if ($this->barber?->user?->avatar) {
            $barberAvatar = app(UserMediaUrl::class)->for($this->barber->user, $this->barber->user->avatar);
        }

        $imageUrl = null;
        if ($this->image) {
            if (str_starts_with($this->image, 'http')) {
                $imageUrl = $this->image;
            } else {
                $normalized = ltrim($this->image, '/');
                if (str_starts_with($normalized, 'storage/')) {
                    $normalized = substr($normalized, strlen('storage/'));
                }
                $imageUrl = url('storage/'.$normalized);
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug ?? Str::slug($this->name),
            'description' => $this->description ?? '',
            'price' => (float) $this->price,
            'home_service_price' => (float) ($this->price * 1.25),
            'duration_minutes' => (int) ($this->duration_minutes ?? 30),
            'category' => $this->category?->name ?? 'General Grooming',
            'category_id' => $this->category_id,
            'category_name' => $this->category?->name ?? 'General Grooming',
            'image' => $imageUrl,
            'image_url' => $imageUrl,
            'is_active' => (bool) $this->is_active,
            'approval_status' => $this->approval_status ?? 'approved',
            'barber_id' => $this->barber_id,
            'barber' => $this->barber ? [
                'id' => $this->barber->id,
                'name' => $this->barber->name ?? $this->barber->user?->name ?? 'Barber',
                'avatar_url' => $barberAvatar,
            ] : null,
            'is_home_service_eligible' => (bool) $this->home_service_allowed,
        ];
    }
}
