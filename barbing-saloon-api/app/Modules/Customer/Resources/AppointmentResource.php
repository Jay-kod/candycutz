<?php

namespace App\Modules\Customer\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'appointment_date' => $this->appointment_date?->format('Y-m-d'),
            'appointment_time' => $this->appointment_time,
            'status' => $this->status?->value ?? $this->status,
            'notes' => $this->notes,
            'total_price' => $this->total_price,
            'deposit_paid' => $this->deposit_paid,
            'deposit_amount' => $this->deposit_amount,
            'service' => $this->whenLoaded('service', fn () => [
                'id' => $this->service->id,
                'name' => $this->service->name,
                'slug' => $this->service->slug,
                'duration_minutes' => $this->service->duration_minutes,
                'price' => $this->service->price,
            ]),
            'barber' => $this->whenLoaded('barber', fn () => [
                'id' => $this->barber->id,
                'name' => $this->barber->user?->name,
            ]),
        ];
    }
}