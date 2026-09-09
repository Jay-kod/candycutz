<?php

namespace App\Modules\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'real_name' => $this->real_name ?? $this->name,
            'username' => $this->username ?? strtolower(explode('@', $this->email)[0]),
            'email' => $this->email,
            'role' => $this->role?->value ?? $this->role,
            'avatar' => $this->avatar,
            'phone' => $this->phone,
            'status' => $this->status ?? 'active',
            'is_active' => (bool) $this->is_active,
            'wallet_balance' => (float) ($this->wallet_balance ?? 0),
        ];

        if ($this->barber) {
            $data['barber_id'] = $this->barber->id;
            $data['chair_status'] = $this->barber->chair_status ?? 'free';
            $data['rating'] = (float) ($this->barber->rating ?? 5.0);
        }

        return $data;
    }
}