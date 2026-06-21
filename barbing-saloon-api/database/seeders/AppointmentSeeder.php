<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

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

        $statuses = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];

        foreach (range(1, 20) as $i) {
            $customer = $customers[($i - 1) % $customers->count()];
            $barber = $barbers[($i - 1) % $barbers->count()];
            $service = $services[($i - 1) % $services->count()];
            $status = $statuses[($i - 1) % count($statuses)];

            Appointment::create([
                'customer_id' => $customer->id,
                'client_name' => $customer->name,
                'client_phone' => $customer->phone ?? '08030000000',
                'client_email' => $customer->email,
                'barber_id' => $barber->id,
                'service_id' => $service->id,
                'appointment_date' => now()->addDays($i - 10)->toDateString(),
                'appointment_time' => sprintf('%02d:00:00', 9 + (($i - 1) % 8)),
                'status' => $status,
                'notes' => null,
                'total_price' => $service->price,
                'deposit_paid' => in_array($status, ['confirmed', 'completed'], true),
                'deposit_amount' => 500,
            ]);
        }
    }
}