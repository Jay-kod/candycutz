<?php

namespace App\Domain\Identity\Actions;

use App\Models\Barber;
use App\Models\Gallery;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;

class DeleteBarber
{
    public function execute(int $id): void
    {
        DB::transaction(function () use ($id) {
            $barber = Barber::with('user')->findOrFail($id);

            // 1. Disassociate all appointments (historical & upcoming) so they become unassigned
            // and financial / transaction records are preserved.
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
