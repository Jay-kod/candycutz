<?php

namespace App\Domain\Identity\Actions;

use App\Models\Barber;

class DeleteBarber
{
    public function execute(int $id): void
    {
        $barber = Barber::with('user')->findOrFail($id);
        if ($barber->user) {
            $barber->user->delete();
        }
        $barber->delete();
    }
}
