<?php

namespace App\Http\Resources;

use App\Domain\Shared\Services\UserMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $u = $this->user;
        $specialties = is_array($this->specialties) ? $this->specialties : (json_decode($this->specialties ?? '[]', true) ?: []);

        $mediaUrl = app(UserMediaUrl::class);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $u?->name ?? 'Master Barber',
            'real_name' => $u?->real_name ?? $u?->name ?? 'Master Barber',
            'username' => $u?->username ?? 'barber',
            'email' => $u?->email ?? '',
            'phone' => $u?->phone ?? '',
            'avatar' => $mediaUrl->for($u, $u?->avatar),
            'avatar_url' => $mediaUrl->for($u, $u?->avatar),
            'cover_image' => $mediaUrl->for($u, $u?->cover_image),
            'cover_image_url' => $mediaUrl->for($u, $u?->cover_image),
            'rating' => (float) ($this->rating ?? 5.0),
            'total_reviews' => (int) ($this->testimonials()->count() ?: 12),
            'experience_years' => (int) ($this->experience_years ?? $this->years_experience ?? 5),
            'chair_status' => $this->chair_status ?? 'free',
            'specialties' => $specialties,
            'bio' => $this->bio ?? 'Expert master barber with precision razor craft.',
            'instagram_url' => $this->instagram_url,
            'status' => $this->status ?? ($this->is_available ? 'active' : 'suspended'),
            'is_available' => (bool) $this->is_available,
            'is_active' => (bool) ($u?->is_active ?? true),
            'working_hours' => $this->whenLoaded('workingHours'),
            'testimonials' => TestimonialResource::collection($this->whenLoaded('testimonials')),
        ];
    }
}
