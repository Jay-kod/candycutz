<?php

namespace App\Domain\Identity\Actions;

use App\Models\Barber;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UpdateBarber
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function execute(int $id, array $data): array
    {
        Gate::authorize('manage-barber-accounts');

        $barber = Barber::with('user')->findOrFail($id);

        $userUpdates = array_intersect_key($data, array_flip(['name', 'email', 'phone']));
        if (! empty($data['password'])) {
            $userUpdates['password'] = Hash::make($data['password']);
        }
        if (! empty($userUpdates) && $barber->user) {
            $barber->user->update($userUpdates);
        }
        if (isset($data['status']) && $barber->user) {
            $barber->user->status = $data['status'];
            $barber->user->save();
        }

        $barberUpdates = [];
        if (isset($data['bio'])) {
            $barberUpdates['bio'] = $data['bio'];
        }
        if (isset($data['experience_years'])) {
            $barberUpdates['years_experience'] = $data['experience_years'];
        }
        if (isset($data['status'])) {
            $barberUpdates['status'] = $data['status'];
        }
        if (isset($data['specialties'])) {
            $barberUpdates['specialties'] = is_string($data['specialties'])
                ? array_filter(array_map('trim', explode(',', $data['specialties'])))
                : $data['specialties'];
        }

        if (! empty($barberUpdates)) {
            $barber->update($barberUpdates);
        }

        return [
            'id' => $barber->id,
            'name' => $barber->user?->name,
            'email' => $barber->user?->email,
            'status' => $barber->status,
        ];
    }
}
