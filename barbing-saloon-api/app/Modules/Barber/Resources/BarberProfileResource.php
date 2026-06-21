<?php

namespace App\Modules\Barber\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BarberProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'phone' => $this->user?->phone,
            'avatar' => $this->user?->avatar,
            'bio' => $this->bio,
            'specialties' => $this->specialties ?? [],
            'years_experience' => $this->years_experience,
            'instagram_url' => $this->instagram_url,
            'is_featured' => $this->is_featured,
        ];
    }
}