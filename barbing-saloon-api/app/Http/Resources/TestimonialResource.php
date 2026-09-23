<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $avatarUrl = null;
        if ($this->client_avatar) {
            $avatarUrl = str_starts_with($this->client_avatar, 'http')
                ? $this->client_avatar
                : url('storage/'.ltrim($this->client_avatar, '/'));
        } elseif ($this->customer?->avatar) {
            $avatarUrl = str_starts_with($this->customer->avatar, 'http')
                ? $this->customer->avatar
                : url('storage/'.ltrim($this->customer->avatar, '/'));
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
