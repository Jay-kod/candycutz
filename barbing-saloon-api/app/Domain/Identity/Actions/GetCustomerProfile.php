<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Services\ConsentService;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\Shared\Services\UserMediaUrl;
use App\Models\Appointment;
use App\Models\User;

class GetCustomerProfile
{
    /**
     * @return array<string, mixed>
     */
    public function execute(int $id): array
    {
        $customer = User::findOrFail($id);
        $appointments = Appointment::query()
            ->with(['service', 'barber.user'])
            ->where('customer_id', $customer->id)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'date' => $a->appointment_date,
                'time' => $a->appointment_time,
                'status' => $a->status,
                'service_name' => $a->service->name ?? 'Custom Service',
                'barber_name' => $a->barber?->user->name ?? 'Master Barber',
                'amount' => (float) $a->total_price,
            ]);

        $totalSpent = (float) Appointment::where('customer_id', $customer->id)
            ->whereIn('status', [AppointmentStatus::confirmed->value, AppointmentStatus::completed->value])
            ->sum('total_price');

        return [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'is_email_verified' => $customer->hasVerifiedEmail(),
                'email_verified_at' => $customer->email_verified_at?->toIso8601String(),
                'phone' => $customer->phone ?? 'N/A',
                'is_active' => (bool) $customer->is_active,
                'status' => $customer->status ?? ($customer->is_active ? 'active' : 'suspended'),
                'auth_provider' => $customer->auth_provider ?: 'local',
                'registered_via' => $customer->auth_provider && $customer->auth_provider !== 'local' ? ucfirst($customer->auth_provider) : 'Registration form',
                'avatar' => app(UserMediaUrl::class)->for($customer, $customer->avatar),
                'no_show_count' => (int) ($customer->no_show_count ?? 0),
                'last_no_show_at' => $customer->last_no_show_at?->toIso8601String(),
                'requires_deposit' => (bool) ($customer->requires_deposit ?? false),
                'created_at' => $customer->created_at?->toIso8601String(),
            ],
            'total_bookings' => $appointments->count(),
            'total_spent' => $totalSpent,
            'appointments' => $appointments,
            'consents' => $customer->consents()
                ->orderByDesc('accepted_at')
                ->get()
                ->map(fn ($consent) => [
                    'type' => $consent->type,
                    'version' => $consent->version,
                    'accepted_at' => $consent->accepted_at?->toIso8601String(),
                    'source' => $consent->source,
                ])
                ->values(),
            'consent_status' => app(ConsentService::class)->statusFor($customer),
        ];
    }
}
