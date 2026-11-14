<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\Shared\Enums\UserRole;
use App\Domain\Shared\Services\UserMediaUrl;
use App\Domain\Shared\Traits\HasAuditLog;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UpdateCustomer
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
        $oldData = [
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
        ];

        if (array_key_exists('name', $data) && ! empty($data['name'])) {
            $customer->name = trim((string) $data['name']);
            $customer->real_name = trim((string) $data['name']);
        }

        if (array_key_exists('email', $data) && ! empty($data['email'])) {
            $customer->email = strtolower(trim((string) $data['email']));
        }

        if (array_key_exists('phone', $data)) {
            $customer->phone = ! empty($data['phone']) ? trim((string) $data['phone']) : null;
        }

        $customer->save();

        $this->logAction('customer.updated', $customer, $oldData, [
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
        ]);

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

