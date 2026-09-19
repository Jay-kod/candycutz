<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Models\User;

class UpdateBarberAccount
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): void
    {
        $userData = array_intersect_key($data, array_flip(['name', 'phone', 'avatar', 'cover_image']));
        if (! empty($userData)) {
            $user->update($userData);
        }

        $barber = $user->barber;
        $barberData = array_intersect_key($data, array_flip([
            'bio',
            'specialties',
            'instagram_url',
            'experience_years',
        ]));
        if (! empty($barberData) && $barber) {
            $barber->update($barberData);
        }
    }
}
