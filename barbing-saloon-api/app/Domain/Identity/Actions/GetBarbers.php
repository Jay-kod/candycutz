<?php

namespace App\Domain\Identity\Actions;

use App\Models\Barber;

class GetBarbers
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(): array
    {
        return Barber::with(['user'])->get()->map(function ($b) {
            return [
                'id' => $b->id,
                'user_id' => $b->user_id,
                'name' => $b->user?->name ?? 'Barber #'.$b->id,
                'email' => $b->user?->email ?? '',
                'phone' => $b->user?->phone ?? '',
                'avatar' => $b->user?->avatar,
                'status' => $b->status ?? ($b->user?->status ?? 'active'),
                'experience_years' => $b->years_experience ?? $b->experience_years ?? 5,
                'specialties' => $b->specialties ?? [],
                'bio' => $b->bio ?? '',
                'rating' => (float) ($b->rating ?: 5.0),
                'is_featured' => (bool) $b->is_featured,
                'is_available' => (bool) $b->is_available,
            ];
        })->all();
    }
}
