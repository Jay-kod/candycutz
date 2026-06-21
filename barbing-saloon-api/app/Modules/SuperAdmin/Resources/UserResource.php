<?php

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
