<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Barber;
use App\Models\Gallery;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteBarber
{
    public function execute(int $id): void
    {
        DB::transaction(function () use ($id) {
            $barber = Barber::with('user')->findOrFail($id);

            // Prevent deletion if the barber has active appointments
            $activeAppointmentsCount = $barber->appointments()
                ->whereIn('status', AppointmentStatus::activeStatuses())
                ->count();

            if ($activeAppointmentsCount > 0) {
                throw ValidationException::withMessages([
                    'barber' => ["Cannot delete barber with {$activeAppointmentsCount} active upcoming appointment(s). Please reassign or cancel them first."],
                ]);
            }

            // 1. Disassociate past/completed/cancelled appointments to preserve financial & audit history
            $barber->appointments()->update(['barber_id' => null]);

            // 2. Disassociate gallery images (keep photos in shop portfolio)
            Gallery::where('barber_id', $barber->id)->update(['barber_id' => null]);

            // 3. Disassociate testimonials (keep reviews for the shop)
            Testimonial::where('barber_id', $barber->id)->update(['barber_id' => null]);

            // 4. Disassociate custom services created by or assigned to this barber
            Service::where('barber_id', $barber->id)->update(['barber_id' => null]);

            // 5. Clean up barber-specific schedules, custom pricing, and blocked slots
            $barber->workingHours()->delete();
            $barber->barberServices()->delete();
            $barber->blockedPeriods()->delete();

            // 6. Delete the barber record before deleting user to release foreign keys
            $user = $barber->user;
            $barber->delete();

            // 7. Revoke tokens and soft-delete user account
            if ($user) {
                $user->tokens()->delete();
                $user->delete();
            }
        });
    }
}
