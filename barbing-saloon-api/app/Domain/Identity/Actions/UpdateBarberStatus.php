<?php

namespace App\Domain\Identity\Actions;

use App\Models\Barber;

class UpdateBarberStatus
{
    /**
     * @return array<string, mixed>
     */
    public function execute(int $id, string $status): array
    {
        $barber = Barber::with('user')->findOrFail($id);
        $barber->update(['status' => $status]);
        if ($barber->user) {
            $barber->user->update(['status' => $status]);
        }

        return ['id' => $barber->id, 'status' => $status];
    }
}
