<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Traits\HasAuditLog;
use App\Models\Barber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateBarber
{
    use HasAuditLog;

    public function __construct(
        protected SetUserPassword $setUserPassword
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(int $id, array $data): Barber
    {
        Gate::authorize('manage-barber-accounts');

        return DB::transaction(function () use ($id, $data): Barber {
            $barber = Barber::with('user')->lockForUpdate()->findOrFail($id);

            $userUpdates = array_intersect_key($data, array_flip(['name', 'email', 'phone']));
            $newPassword = isset($data['password']) && is_string($data['password']) && $data['password'] !== ''
                ? $data['password']
                : null;

            if ($barber->user) {
                if (! empty($userUpdates)) {
                    $barber->user->update($userUpdates);
                }

                if ($newPassword !== null) {
                    $this->setUserPassword->execute($barber->user, $newPassword);
                    $this->logAction('barber.password_reset', $barber->user, [], [
                        'email' => $barber->user->email,
                        'admin_override' => true,
                    ]);
                }
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
        });
    }
}
