<?php

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceZone;
use App\Models\User;
use App\Models\WorkingHour;

it('constructs the core booking graph with factories', function () {
    $business = Business::factory()->make();
    $branch = Branch::factory()->state(['business_id' => 1])->make();
    $category = ServiceCategory::factory()->make();
    $service = Service::factory()->state(['branch_id' => 1, 'category_id' => 1])->make();
    $barber = Barber::factory()->state(['user_id' => 1, 'branch_id' => 1])->make();
    $zone = ServiceZone::factory()->state(['branch_id' => 1])->make();
    $workingHour = WorkingHour::factory()->state(['barber_id' => 1])->make();
    $customer = User::factory()->customer()->make();
    $appointment = Appointment::factory()
        ->state([
            'branch_id' => 1,
            'customer_id' => 1,
            'barber_id' => 1,
            'service_id' => 1,
            'service_zone_id' => 1,
        ])
        ->confirmed()
        ->make();
    $payment = Payment::factory()->state([
        'appointment_id' => 1,
        'customer_id' => 1,
    ])->make();

    expect($business)->toBeInstanceOf(Business::class)
        ->and($service)->toBeInstanceOf(Service::class)
        ->and($workingHour)->toBeInstanceOf(WorkingHour::class)
        ->and($appointment->status->value)->toBe('confirmed')
        ->and($payment)->toBeInstanceOf(Payment::class);
});
