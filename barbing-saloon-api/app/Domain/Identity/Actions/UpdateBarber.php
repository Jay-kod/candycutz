<?php

namespace App\Domain\Identity\Actions;

use App\Models\Barber;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UpdateBarber
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(int $id, array $data): Barber
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

        $barberUpdates = [];
        if (isset($data['bio'])) {
            $barberUpdates['bio'] = $data['bio'];
        }
        if (isset($data['experience_years'])) {
            $barberUpdates['years_experience'] = (int) $data['experience_years'];
            $barberUpdates['experience_years'] = (int) $data['experience_years'];
        }
        if (isset($data['status'])) {
            $barberUpdates['status'] = $data['status'];
        }
        if (isset($data['specialties'])) {
            $barberUpdates['specialties'] = is_string($data['specialties'])
                ? array_filter(array_map('trim', explode(',', $data['specialties'])))
                : $data['specialties'];
        }
        if (array_key_exists('bank_name', $data)) {
            $barberUpdates['bank_name'] = $data['bank_name'];
        }
        if (array_key_exists('account_name', $data)) {
            $barberUpdates['account_name'] = $data['account_name'];
        }
        if (array_key_exists('account_number', $data)) {
            $barberUpdates['account_number'] = $data['account_number'];
        }

        if (! empty($barberUpdates)) {
            $barber->update($barberUpdates);
        }

        return $barber->refresh()->load(['user', 'workingHours', 'testimonials']);
    }
}
