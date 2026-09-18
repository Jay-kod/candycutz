<?php

namespace App\Domain\Booking\Actions;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateWalkInAppointment
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Appointment
    {
        $service = Service::findOrFail($data['service_id']);
        $barber = Barber::findOrFail($data['barber_id']);

        $customerName = $data['customer_name'] ?? 'Walk-in Client';
        $customerPhone = $data['customer_phone'] ?? '';
        $customerEmail = $data['customer_email'] ?? 'walkin@candycutz.com';

        $user = User::where('phone', $customerPhone)->orWhere('email', $customerEmail)->first();
        if (! $user) {
            $user = User::create([
                'name' => $customerName,
                'email' => $customerEmail ?: 'walkin_'.time().'@candycutz.com',
                'phone' => $customerPhone,
                'password' => Hash::make(uniqid()),
                'role' => 'customer',
                'is_active' => true,
            ]);
        }

        $apptDate = $data['appointment_date'] ?? today()->toDateString();
        $apptTime = $data['appointment_time'] ?? now()->format('H:i');

        return Appointment::create([
            'customer_id' => $user->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'client_name' => $customerName,
            'client_phone' => $customerPhone,
            'client_email' => $customerEmail,
            'appointment_date' => $apptDate,
            'appointment_time' => $apptTime,
            'status' => AppointmentStatus::confirmed->value,
            'total_price' => $service->price,
            'deposit_paid' => true,
            'verification_code' => 'CC-'.strtoupper(substr(uniqid(), -6)),
            'notes' => 'Walk-in booking',
        ]);
    }
}
