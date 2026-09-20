<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->orderBy('id')->get();
        $barbers = Barber::orderBy('id')->get();
        $services = Service::orderBy('id')->get();

        if ($customers->isEmpty() || $barbers->isEmpty() || $services->isEmpty()) {
            return;
        }

        // Clean existing test appointments and payments
        Appointment::withTrashed()->forceDelete();
        Payment::query()->delete();

        $scheduleOffsets = [
            // Today (4 appointments)
            ['days' => 0, 'time' => '09:00:00', 'status' => 'completed', 'type' => 'in_shop'],
            ['days' => 0, 'time' => '11:30:00', 'status' => 'completed', 'type' => 'walk_in'],
            ['days' => 0, 'time' => '14:00:00', 'status' => 'confirmed', 'type' => 'in_shop'],
            ['days' => 0, 'time' => '16:30:00', 'status' => 'pending', 'type' => 'in_shop'],

            // Yesterday (3 appointments)
            ['days' => -1, 'time' => '10:00:00', 'status' => 'completed', 'type' => 'in_shop'],
            ['days' => -1, 'time' => '13:00:00', 'status' => 'completed', 'type' => 'walk_in'],
            ['days' => -1, 'time' => '15:30:00', 'status' => 'cancelled', 'type' => 'in_shop'],

            // Past 2 - 14 days (12 appointments)
            ['days' => -2, 'time' => '10:00:00', 'status' => 'completed', 'type' => 'in_shop'],
            ['days' => -3, 'time' => '11:00:00', 'status' => 'completed', 'type' => 'in_shop'],
            ['days' => -4, 'time' => '14:00:00', 'status' => 'completed', 'type' => 'walk_in'],
            ['days' => -5, 'time' => '09:30:00', 'status' => 'completed', 'type' => 'in_shop'],
            ['days' => -6, 'time' => '12:00:00', 'status' => 'completed', 'type' => 'in_shop'],
            ['days' => -7, 'time' => '15:00:00', 'status' => 'completed', 'type' => 'in_shop'],
            ['days' => -8, 'time' => '11:00:00', 'status' => 'completed', 'type' => 'walk_in'],
            ['days' => -10, 'time' => '16:00:00', 'status' => 'completed', 'type' => 'in_shop'],
            ['days' => -12, 'time' => '10:30:00', 'status' => 'completed', 'type' => 'in_shop'],
            ['days' => -14, 'time' => '13:30:00', 'status' => 'completed', 'type' => 'in_shop'],
            ['days' => -18, 'time' => '15:00:00', 'status' => 'no_show', 'type' => 'in_shop'],
            ['days' => -22, 'time' => '11:00:00', 'status' => 'completed', 'type' => 'in_shop'],

            // Upcoming days (6 appointments)
            ['days' => 1, 'time' => '10:00:00', 'status' => 'confirmed', 'type' => 'in_shop'],
            ['days' => 1, 'time' => '14:00:00', 'status' => 'pending', 'type' => 'in_shop'],
            ['days' => 2, 'time' => '11:00:00', 'status' => 'confirmed', 'type' => 'in_shop'],
            ['days' => 3, 'time' => '15:30:00', 'status' => 'pending', 'type' => 'in_shop'],
            ['days' => 5, 'time' => '12:00:00', 'status' => 'confirmed', 'type' => 'in_shop'],
            ['days' => 7, 'time' => '16:00:00', 'status' => 'pending', 'type' => 'in_shop'],
        ];

        foreach ($scheduleOffsets as $idx => $slot) {
            $customer = $customers[$idx % $customers->count()];
            $barber = $barbers[$idx % $barbers->count()];
            $service = $services[$idx % $services->count()];
            $isPaid = in_array($slot['status'], ['confirmed', 'completed'], true);
            $date = now()->addDays($slot['days'])->toDateString();

            $appt = Appointment::create([
                'booking_reference' => 'CC-'.strtoupper(Str::random(8)),
                'customer_id' => $customer->id,
                'client_name' => $slot['type'] === 'walk_in' ? 'Walk-In ('.$customer->name.')' : $customer->name,
                'client_phone' => $customer->phone ?? '08030000000',
                'client_email' => $customer->email,
                'barber_id' => $barber->id,
                'service_id' => $service->id,
                'appointment_type' => 'in_shop',
                'appointment_date' => $date,
                'appointment_time' => $slot['time'],
                'end_time' => date('H:i:s', strtotime($slot['time']) + ($service->duration_minutes * 60)),
                'total_duration_minutes' => $service->duration_minutes,
                'status' => $slot['status'],
                'notes' => $slot['type'] === 'walk_in' ? 'Walk-in customer handled by reception' : null,
                'total_price' => $service->price,
                'total_amount' => $service->price,
                'grand_total' => $service->price,
                'deposit_paid' => $isPaid,
                'deposit_amount' => $isPaid ? min(2000, (int) $service->price) : 0,
            ]);

            if ($isPaid) {
                Payment::create([
                    'appointment_id' => $appt->id,
                    'customer_id' => $customer->id,
                    'amount' => (int) $service->price,
                    'currency' => 'NGN',
                    'status' => 'verified',
                    'payment_method' => $slot['type'] === 'walk_in' ? 'cash' : 'card',
                    'transaction_ref' => 'TXN-'.strtoupper(Str::random(10)),
                    'verified_at' => now()->addDays($slot['days']),
                    'verified_by_user_id' => 1,
                ]);
            }
        }
    }
}
