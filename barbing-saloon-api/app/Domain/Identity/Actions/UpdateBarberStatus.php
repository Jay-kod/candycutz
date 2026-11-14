<?php

namespace App\Domain\Identity\Actions;

use App\Models\Barber;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UpdateBarberStatus
{
    public function execute(int $id, string $status): Barber
    {
        Gate::authorize('manage-barber-accounts');

        if (! in_array($status, Barber::STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => 'The selected barber status is invalid.',
            ]);
        }

        $barber = Barber::with('user')->findOrFail($id);
        $barber->update(['status' => $status]);

        return $barber->refresh();
    }
}
