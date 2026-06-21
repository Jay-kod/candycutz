<?php

namespace App\Modules\Customer\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'client_name' => $this->client_name,
            'rating' => $this->rating,
            'review' => $this->review,
            'is_approved' => $this->is_approved,
            'is_featured' => $this->is_featured,
            'service' => $this->whenLoaded('service', fn () => [
                'id' => $this->service->id,
                'name' => $this->service->name,
            ]),
            'barber' => $this->whenLoaded('barber', fn () => [
                'id' => $this->barber->id,
                'name' => $this->barber->user?->name,
            ]),
        ];
    }
}