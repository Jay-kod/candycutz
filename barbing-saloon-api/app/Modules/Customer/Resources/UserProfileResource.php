<?php

namespace App\Modules\Customer\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'role' => $this->role?->value ?? $this->role,
            'bookings_count' => $this->whenCounted('appointments'),
            'reviews_count' => $this->whenCounted('testimonials'),
        ];
    }
}