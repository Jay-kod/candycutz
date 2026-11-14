<?php

declare(strict_types=1);

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;

it('retrieves checkout data with bank details, barbers, and services', function () {
    $barber = Barber::factory()->create();
    $service = Service::factory()->create(['price' => 30000]);

    $response = $this->getJson('/api/v1/payments/checkout-data');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'bank_details' => [
                'bank_name',
                'account_name',
                'account_number',
            ],
            'payment_settings' => [
                'gateway_title',
                'expiry_minutes',
                'notice_text',
                'enabled_channels',
                'ussd_code_template',
                'auto_confirm',
            ],
            'barbers',
            'services',
        ],
    ]);
});

it('confirms transfer payment, saves to database, updates appointment and reflects for selected barber', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $barber = Barber::factory()->create();
    $service = Service::factory()->create(['price' => 30000]);

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'grand_total' => 30000,
        'total_amount' => 30000,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'deposit_paid' => false,
    ]);

    $response = $this->actingAs($customer)->postJson('/api/v1/payments/confirm-transfer', [
        'appointment_id' => $appointment->id,
        'barber_id' => $barber->id,
        'payment_method' => 'bank_transfer',
        'amount' => 30000,
        'reference' => 'TEST-REF-12345',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('data.payment_status', 'paid');
    $response->assertJsonPath('data.barber.id', $barber->id);

    // Verify saved in DB
    $payment = Payment::where('appointment_id', $appointment->id)->first();
    expect($payment)->not->toBeNull();
    expect($payment->status->value)->toBe('successful');
    expect($payment->payment_method)->toBe('bank_transfer');
    expect((int) $payment->amount)->toBe(30000);

    // Verify appointment in DB updated
    $freshAppointment = $appointment->fresh();
    expect($freshAppointment->payment_status->value)->toBe('paid');
    expect($freshAppointment->status->value)->toBe('confirmed');
    expect($freshAppointment->deposit_paid)->toBeTrue();
    expect($freshAppointment->barber_id)->toBe($barber->id);
});

it('allows admin to control payment gateway settings and reflect them on checkout', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $payload = [
        'bank_name' => 'Sterling Bank VIP',
        'account_name' => 'NextGen PG Official',
        'account_number' => '5261935770',
        'instructions' => 'Include your full booking reference in the transfer remarks.',
        'payment_gateway_title' => 'CandyCutz VIP Registration',
        'payment_expiry_minutes' => 90,
        'payment_notice_text' => 'Custom admin alert notice for transfer payment.',
        'payment_channels' => ['bank_transfer', 'ussd'],
        'ussd_code_template' => '*737*000*5261935770#',
        'auto_confirm' => true,
    ];

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/bank-details', $payload);
    $response->assertStatus(200);
    $response->assertJsonPath('data.bank_name', 'Sterling Bank VIP');
    $response->assertJsonPath('data.payment_gateway_title', 'CandyCutz VIP Registration');
    $response->assertJsonPath('data.payment_expiry_minutes', 90);
    $response->assertJsonPath('data.payment_channels', ['bank_transfer', 'ussd']);

    // Now test public /api/v1/payments/checkout-data reflects what admin configured
    $checkoutResponse = $this->getJson('/api/v1/payments/checkout-data');
    $checkoutResponse->assertStatus(200);
    $checkoutResponse->assertJsonPath('data.bank_details.bank_name', 'Sterling Bank VIP');
    $checkoutResponse->assertJsonPath('data.payment_settings.gateway_title', 'CandyCutz VIP Registration');
    $checkoutResponse->assertJsonPath('data.payment_settings.expiry_minutes', 90);
    $checkoutResponse->assertJsonPath('data.payment_settings.enabled_channels', ['bank_transfer', 'ussd']);
});

it('allows admin to verify payment and reassign barber on appointments', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);
    $barberA = Barber::factory()->create();
    $barberB = Barber::factory()->create();
    $service = Service::factory()->create(['price' => 25000]);

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barberA->id,
        'service_id' => $service->id,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'deposit_paid' => false,
    ]);

    $payment = Payment::factory()->create([
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'amount' => 25000,
        'status' => 'pending',
        'provider' => 'manual_transfer',
        'payment_method' => 'bank_transfer',
    ]);

    // Admin verifies payment
    $verifyRes = $this->actingAs($admin)->postJson("/api/v1/admin/payments/{$payment->id}/verify");
    $verifyRes->assertStatus(200);

    expect($payment->fresh()->status->value)->toBe('successful');
    expect($appointment->fresh()->payment_status->value)->toBe('paid');
    expect($appointment->fresh()->status->value)->toBe('confirmed');

    // Admin reassigns barber from barberA to barberB
    $reassignRes = $this->actingAs($admin)->postJson("/api/v1/admin/payments/{$payment->id}/reassign-barber", [
        'barber_id' => $barberB->id,
    ]);
    $reassignRes->assertStatus(200);

    expect($appointment->fresh()->barber_id)->toBe($barberB->id);
});
