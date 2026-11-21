<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\Shared\Enums\UserRole;
use App\Domain\Shared\Services\UserMediaUrl;
use App\Domain\Shared\Traits\HasAuditLog;
use App\Events\UserSuspended;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UpdateCustomerStatus
{
    use HasAuditLog;

    public function __construct(
        protected UserMediaUrl $mediaUrl
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function execute(int $id, array $data): array
    {
        Gate::authorize('manage-customers');

        /** @var User $customer */
        $customer = User::where('role', UserRole::customer)->findOrFail($id);
        $oldStatus = [
            'is_active' => $customer->is_active,
            'status' => $customer->status,
        ];

        $newActive = null;
        if (array_key_exists('is_active', $data)) {
            $newActive = (bool) $data['is_active'];
        } elseif (array_key_exists('status', $data)) {
            $newActive = ($data['status'] === 'active');
        }

        if ($newActive !== null) {
            $customer->is_active = $newActive;
            $customer->status = $newActive ? 'active' : 'suspended';

            if (! $newActive) {
                $customer->deactivated_at = now();
                $customer->tokens()->delete();
                UserSuspended::dispatch($customer, auth()->user(), 'Admin suspended customer account');
            } else {
                $customer->deactivated_at = null;
            }

            $customer->save();

            $this->logAction('customer.status_updated', $customer, $oldStatus, [
                'is_active' => $customer->is_active,
                'status' => $customer->status,
            ]);
        }

        $totalSpent = (float) Appointment::where('customer_id', $customer->id)
            ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
            ->sum('total_price');

        $lastBooking = Appointment::where('customer_id', $customer->id)
            ->latest('appointment_date')
            ->value('appointment_date');

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone ?? 'N/A',
            'is_active' => (bool) $customer->is_active,
            'status' => $customer->status ?? ($customer->is_active ? 'active' : 'suspended'),
            'auth_provider' => $customer->auth_provider ?: 'local',
            'registered_via' => $customer->auth_provider && $customer->auth_provider !== 'local' ? ucfirst($customer->auth_provider) : 'Registration form',
            'avatar' => $this->mediaUrl->for($customer, $customer->avatar),
            'created_at' => $customer->created_at?->toIso8601String(),
            'total_bookings' => $customer->appointments()->count(),
            'total_spent' => $totalSpent,
            'last_booking_date' => $lastBooking,
        ];
    }
}

