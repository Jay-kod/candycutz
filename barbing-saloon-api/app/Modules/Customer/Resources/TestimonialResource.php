<?php

namespace App\Modules\Customer\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'client_name' => $this->customer?->name ?? 'Anonymous',
            'rating' => $this->rating,
            'review' => $this->comment,
            'is_approved' => $this->is_approved,
            'barber' => $this->whenLoaded('barber', fn () => [
                'id' => $this->barber->id,
                'name' => $this->barber->user?->name,
            ]),
        ];
    }
}