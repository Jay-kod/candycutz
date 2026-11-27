<?php

namespace App\Http\Resources;

use App\Domain\Shared\Services\UserMediaUrl;
use App\Models\Barber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Barber
 */
class BarberResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $u = $this->user;
        $specialties = is_array($this->specialties) ? $this->specialties : (json_decode((string) ($this->specialties ?? '[]'), true) ?: []);

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
            'avatar_thumb_url' => $mediaUrl->thumbnailFor($u, $u->avatar),
            'cover_image' => $mediaUrl->for($u, $u?->cover_image),
            'cover_image_url' => $mediaUrl->for($u, $u?->cover_image),
            'cover_image_thumb_url' => $mediaUrl->thumbnailFor($u, $u->cover_image),
            'rating' => (float) ($this->rating ?? 5.0),
            'total_reviews' => (int) ($this->whenLoaded('testimonials') ? $this->testimonials->count() : 12),
            'experience_years' => (int) ($this->experience_years ?? $this->years_experience ?? 5),
            'chair_status' => $this->chair_status ?? 'free',
            'specialties' => $specialties,
            'bio' => $this->bio ?? 'Expert master barber with precision razor craft.',
            'instagram_url' => $this->instagram_url,
            'bank_name' => $this->bank_name,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'status' => $this->status ?? ($this->is_available ? 'active' : 'suspended'),
            'is_available' => (bool) $this->is_available,
            'is_active' => (bool) ($u?->is_active ?? true),
            'working_hours' => $this->whenLoaded('workingHours', function () {
                return $this->workingHours->map(function ($wh) {
                    $open = substr((string) ($wh->open_time ?? '08:00'), 0, 5);
                    $close = substr((string) ($wh->close_time ?? '20:00'), 0, 5);
                    $isClosed = (bool) $wh->is_closed;

                    return [
                        'id' => $wh->id,
                        'barber_id' => $wh->barber_id,
                        'day_of_week' => (int) $wh->day_of_week,
                        'open_time' => $open,
                        'close_time' => $close,
                        'start_time' => $open,
                        'end_time' => $close,
                        'is_closed' => $isClosed,
                        'is_working' => ! $isClosed,
                    ];
                });
            }),
            'testimonials' => $this->whenLoaded('testimonials') ? TestimonialResource::collection($this->testimonials) : [],
        ];
    }
}
