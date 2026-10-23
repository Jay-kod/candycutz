<?php

namespace App\Http\Resources;

use App\Domain\Shared\Services\UserMediaUrl;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Testimonial
 */
class TestimonialResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $avatarUrl = null;
        if ($this->client_avatar) {
            $avatarUrl = app(UserMediaUrl::class)->for($this->customer, $this->client_avatar);
            if (! $avatarUrl && ! str_starts_with(ltrim($this->client_avatar, '/'), 'user-media/')) {
                if (str_starts_with($this->client_avatar, 'http')) {
                    $avatarUrl = $this->client_avatar;
                } else {
                    $normalized = ltrim($this->client_avatar, '/');
                    if (str_starts_with($normalized, 'storage/')) {
                        $normalized = substr($normalized, strlen('storage/'));
                    }
                    $avatarUrl = url('storage/'.$normalized);
                }
            }
        } elseif ($this->customer?->avatar) {
            $avatarUrl = app(UserMediaUrl::class)->for($this->customer, $this->customer->avatar);
        }

        return [
            'id' => $this->id,
            'customer_name' => $this->client_name ?? $this->customer?->name ?? 'Anonymous',
            'avatar_url' => $avatarUrl,
            'rating' => (int) $this->rating,
            'review' => $this->review ?? $this->comment ?? '',
            'comment' => $this->review ?? $this->comment ?? '',
            'service_id' => $this->service_id,
            'service' => $this->service ? [
                'id' => $this->service->id,
                'name' => $this->service->name,
                'slug' => $this->service->slug ?? null,
                'price' => (float) $this->service->price,
            ] : null,
            'barber_id' => $this->barber_id,
            'barber' => $this->barber ? [
                'id' => $this->barber->id,
                'name' => $this->barber->name ?? $this->barber->user?->name ?? 'Unknown',
            ] : null,
            'created_at' => $this->created_at?->toISOString(),
            'is_approved' => (bool) $this->is_approved,
            'is_featured' => (bool) $this->is_featured,
        ];
    }
}
