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
        $avatarUrl = $mediaUrl->for($this->resource, $this->avatar);
        $coverImageUrl = $mediaUrl->for($this->resource, $this->cover_image);

        $data['avatar'] = $avatarUrl;
        $data['avatar_url'] = $avatarUrl;
        $data['avatar_thumb_url'] = $mediaUrl->thumbnailFor($this->resource, $this->avatar);
        $data['cover_image'] = $coverImageUrl;
        $data['cover_image_url'] = $coverImageUrl;
        $data['no_show_count'] = (int) ($this->no_show_count ?? 0);
        $data['last_no_show_at'] = $this->last_no_show_at?->toIso8601String();
        $data['requires_deposit'] = (bool) ($this->requires_deposit ?? false);

        return $data;
    }
}
