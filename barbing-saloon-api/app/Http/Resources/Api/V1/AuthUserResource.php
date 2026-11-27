<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\Identity\Services\ConsentService;
use App\Domain\Shared\Services\UserMediaUrl;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class AuthUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $mediaUrl = app(UserMediaUrl::class);
        $avatarUrl = $mediaUrl->for($this->resource, $this->avatar);
        $coverImageUrl = $mediaUrl->for($this->resource, $this->cover_image);

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'real_name' => $this->real_name ?? $this->name,
            'username' => $this->username ?? strtolower(explode('@', $this->email)[0]),
            'email' => $this->email,
            'role' => $this->role->value ?? $this->role,
            'avatar' => $avatarUrl,
            'avatar_url' => $avatarUrl,
            'cover_image' => $coverImageUrl,
            'cover_image_url' => $coverImageUrl,
            'phone' => $this->phone,
            'status' => $this->status ?? 'active',
            'is_active' => (bool) $this->is_active,
            'email_verified_at' => $this->email_verified_at instanceof CarbonInterface
                ? $this->email_verified_at->toIso8601String()
                : ($this->email_verified_at ? (string) $this->email_verified_at : null),
            'is_email_verified' => $this->hasVerifiedEmail(),
            'no_show_count' => (int) ($this->no_show_count ?? 0),
            'last_no_show_at' => $this->last_no_show_at?->toIso8601String(),
            'requires_deposit' => (bool) ($this->requires_deposit ?? false),
        ];

        if ($this->barber) {
            $data['barber_id'] = $this->barber->id;
            $data['chair_status'] = $this->barber->chair_status ?? 'free';
            $data['rating'] = (float) ($this->barber->rating ?? 5.0);
        }

        $consent = app(ConsentService::class)->statusFor($this->resource);
        $data['consent_required'] = $consent['required'];
        $data['consent'] = $consent;

        return $data;
    }
}
