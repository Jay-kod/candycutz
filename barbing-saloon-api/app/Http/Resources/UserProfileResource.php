<?php

namespace App\Http\Resources;

use App\Domain\Shared\Services\UserMediaUrl;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $data */
        $data = parent::toArray($request);
        $mediaUrl = app(UserMediaUrl::class);
        $data['avatar'] = $mediaUrl->for($this->resource, $this->avatar);
        $data['cover_image'] = $mediaUrl->for($this->resource, $this->cover_image);

        return $data;
    }
}
