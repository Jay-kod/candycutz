<?php

namespace App\Domain\Identity\Actions;

use App\Models\Barber;
use Illuminate\Support\Facades\Gate;

class UpdateBarberStatus
{
    /**
     * @return array<string, mixed>
     */
    public function execute(int $id, string $status): array
    {
        Gate::authorize('manage-barber-accounts');

        $barber = Barber::with('user')->findOrFail($id);
        $barber->update(['status' => $status]);
        if ($barber->user) {
            $barber->user->status = $status;
            $barber->user->save();
        }

        return ['id' => $barber->id, 'status' => $status];
    }
}
