<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Shared\Services\UserMediaUrl;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray($request): array
    {
        $user = $this->resource;

        $avatarUrl = app(UserMediaUrl::class)->for($user, $user->avatar);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $avatarUrl,
            'avatar_url' => $avatarUrl,
            'role' => $user->role instanceof \BackedEnum ? $user->role->value : $user->role,
            'is_active' => (bool) ($user->is_active ?? false),
            'created_at' => $user->created_at,
        ];
    }
}
