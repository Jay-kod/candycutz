<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Barber;

class GetBarberDashboard
{
    /**
     * @return array<string, mixed>
     */
    public function execute(Barber $barber): array
    {
        $today = today()->toDateString();

        $todayAppointments = Appointment::with(['service', 'customer'])
            ->where('barber_id', $barber->id)
            ->where('appointment_date', $today)
            ->orderBy('appointment_time')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'client_name' => $a->customer?->name ?? $a->client_name ?? 'Client',
                'customer_name' => $a->customer?->name ?? $a->client_name ?? 'Client',
                'appointment_time' => substr((string) $a->appointment_time, 0, 5),
                'appointment_date' => $a->appointment_date,
                'status' => $a->status?->value ?? $a->status,
                'verification_code' => $a->verification_code,
                'service' => [
                    'id' => $a->service?->id,
                    'name' => $a->service?->name ?? 'General Service',
                    'price' => (float) ($a->service?->price ?? $a->total_price ?? 0),
                ],
                'price' => (float) ($a->total_price ?? $a->service?->price ?? 0),
            ]);

        $todayBookingsCount = Appointment::where('barber_id', $barber->id)
            ->where('appointment_date', $today)
            ->count();

        $upcomingCount = Appointment::where('barber_id', $barber->id)
            ->where('appointment_date', '>=', $today)
            ->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
            ->count();

        $completedToday = Appointment::where('barber_id', $barber->id)
            ->where('appointment_date', $today)
            ->where('status', AppointmentStatus::completed->value)
            ->count();

        $noShowCount = Appointment::where('barber_id', $barber->id)
            ->where('status', AppointmentStatus::no_show->value)
            ->count();

        // Pending payments: bookings assigned to this barber where payment is awaiting verification or pending
        $pendingPayments = Appointment::with(['service', 'customer', 'payment'])
            ->where('barber_id', $barber->id)
            ->where(function ($q) {
                $q->whereHas('payment', function ($pq) {
                    $pq->whereIn('status', ['awaiting_verification', 'pending']);
                })->orWhere(function ($sq) {
                    $sq->where('status', AppointmentStatus::pending->value)
                       ->whereNotNull('notes');
                });
            })
            ->latest('appointment_date')
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'customer_name' => $a->customer?->name ?? $a->client_name ?? 'Client',
                'customer_email' => $a->customer?->email ?? $a->client_email ?? '',
                'customer_phone' => $a->customer?->phone ?? $a->client_phone ?? '',
                'price' => (float) ($a->total_price ?? $a->service?->price ?? 0),
                'service_name' => $a->service?->name ?? 'General Service',
                'duration_minutes' => $a->service?->duration_minutes ?? 30,
                'receipt_image' => $a->payment?->receipt_image ?? $a->payment?->receipt_url ?? null,
                'appointment_date' => $a->appointment_date,
                'appointment_time' => substr((string) $a->appointment_time, 0, 5),
                'status' => $a->status?->value ?? $a->status,
                'payment_status' => $a->payment?->status ?? 'awaiting_verification',
            ]);

        return [
            'today_appointments' => $todayAppointments,
            'pending_payments' => $pendingPayments,
            'stats' => [
                'today_bookings' => $todayBookingsCount,
                'upcoming_bookings' => $upcomingCount,
                'completed_bookings' => $completedToday,
                'no_show_count' => $noShowCount,
                // Legacy keys
                'upcoming' => $upcomingCount,
                'completed_today' => $completedToday,
            ],
        ];
    }
}
