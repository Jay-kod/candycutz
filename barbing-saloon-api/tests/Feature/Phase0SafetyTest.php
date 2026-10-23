<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('payments.default', 'paystack');
    Config::set('payments.gateways.paystack.secret_key', 'sk_test_mock_secret_key_12345');
    Config::set('payments.gateways.paystack.public_key', 'pk_test_mock_public_key_12345');

    Http::fake([
        'https://api.paystack.co/transaction/initialize' => Http::response([
            'status' => true,
            'message' => 'Authorization URL created',
            'data' => [
                'authorization_url' => 'https://checkout.paystack.com/mock_auth_url',
                'access_code' => 'mock_access_code_123',
                'reference' => 'PAYSTACK_REF_MOCK',
            ],
        ], 200),
    ]);
});

it('ignores client-supplied amount in checkout and computes server-side amount', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $barber = Barber::factory()->create();
    $service = Service::factory()->create(['price' => 750000]); // 7,500 NGN in kobo

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'grand_total' => 750000,
        'total_price' => 750000,
        'status' => AppointmentStatus::pending->value,
        'deposit_paid' => false,
    ]);

    // Customer maliciously sends amount: 100 (1 NGN)
    $response = $this->actingAs($customer)->postJson('/api/v1/payments/checkout', [
        'appointment_id' => $appointment->id,
        'payment_method' => 'paystack',
        'amount' => 100,
    ]);

    $response->assertStatus(200);

    // Verify stored payment record uses server-side grand_total (750,000 kobo), NOT the client's 100
    $this->assertDatabaseHas('payments', [
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'amount' => 750000,
    ]);

    $this->assertDatabaseMissing('payments', [
        'appointment_id' => $appointment->id,
        'amount' => 100,
    ]);
});

it('prohibits barber B from verifying barber A\'s appointment payment', function () {
    $userBarberA = User::factory()->create(['role' => 'barber']);
    $barberA = Barber::factory()->create(['user_id' => $userBarberA->id]);

    $userBarberB = User::factory()->create(['role' => 'barber']);
    $barberB = Barber::factory()->create(['user_id' => $userBarberB->id]);

    $customer = User::factory()->create(['role' => 'customer']);
    $service = Service::factory()->create();

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barberA->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::pending->value,
    ]);

    $payment = Payment::create([
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'amount' => 500000,
        'currency' => 'NGN',
        'status' => 'under_review',
        'payment_method' => 'manual_transfer',
        'transaction_ref' => 'MANUAL_TEST_REF_1',
    ]);

    // Barber B attempts to verify Barber A's appointment
    $response = $this->actingAs($userBarberB)->postJson("/api/v1/barber/bookings/{$appointment->id}/verify-payment", [
        'action' => 'approve',
    ]);

    $response->assertStatus(403);
    $response->assertJson([
        'success' => false,
        'error' => [
            'code' => 'FORBIDDEN',
        ],
    ]);

    // Barber A verifies their own appointment
    $responseA = $this->actingAs($userBarberA)->postJson("/api/v1/barber/bookings/{$appointment->id}/verify-payment", [
        'action' => 'approve',
    ]);

    $responseA->assertStatus(200);
    expect($appointment->fresh()->status->value)->toBe(AppointmentStatus::confirmed->value);
});

it('never returns a placeholder bank account and returns 422 when bank details are unconfigured', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $barberUser = User::factory()->create(['role' => 'barber']);
    $barber = Barber::factory()->create([
        'user_id' => $barberUser->id,
    ]);

    $service = Service::factory()->create();
    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
    ]);

    // Ensure config has no fallback accounts
    Config::set('payments.gateways.manual.bank_accounts', []);
    Config::set('payments.gateways.manual_transfer.default_account_number', null);
    Config::set('payments.gateways.manual_transfer.default_bank_name', null);

    $response = $this->actingAs($customer)->getJson("/api/v1/payments/appointments/{$appointment->id}/details");

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'error' => [
            'code' => 'PAYMENT_DETAILS_NOT_CONFIGURED',
        ],
    ]);

    // Verify placeholder strings never appear
    $content = $response->getContent();
    expect(str_contains($content, '0123456789'))->toBeFalse();
    expect(str_contains($content, '0000000000'))->toBeFalse();
});

it('rejects appointments booked with retired payment methods like stripe or wallet', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $barber = Barber::factory()->create();
    $service = Service::factory()->create();

    // Booking with retired wallet
    $responseWallet = $this->actingAs($customer)->postJson('/api/v1/appointments', [
        'service_id' => $service->id,
        'barber_id' => $barber->id,
        'appointment_date' => now()->addDay()->toDateString(),
        'appointment_time' => '10:00:00',
        'payment_method' => 'wallet',
    ]);
    $responseWallet->assertStatus(422);
    $responseWallet->assertJsonValidationErrors(['payment_method']);

    // Booking with retired stripe
    $responseStripe = $this->actingAs($customer)->postJson('/api/v1/appointments', [
        'service_id' => $service->id,
        'barber_id' => $barber->id,
        'appointment_date' => now()->addDay()->toDateString(),
        'appointment_time' => '10:00:00',
        'payment_method' => 'stripe',
    ]);
    $responseStripe->assertStatus(422);
    $responseStripe->assertJsonValidationErrors(['payment_method']);

    // Booking with paystack succeeds
    $responsePaystack = $this->actingAs($customer)->postJson('/api/v1/appointments', [
        'service_id' => $service->id,
        'barber_id' => $barber->id,
        'appointment_date' => now()->addDay()->toDateString(),
        'appointment_time' => '10:00:00',
        'payment_method' => 'paystack',
    ]);
    $responsePaystack->assertStatus(201);
});

it('handles reject in verifyPayment by cancelling appointment, creating status history and audit row', function () {
    $userBarber = User::factory()->create(['role' => 'barber']);
    $barber = Barber::factory()->create(['user_id' => $userBarber->id]);
    $customer = User::factory()->create(['role' => 'customer']);
    $service = Service::factory()->create();

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::pending->value,
    ]);

    $payment = Payment::create([
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'amount' => 500000,
        'currency' => 'NGN',
        'status' => 'under_review',
        'payment_method' => 'manual_transfer',
        'transaction_ref' => 'MANUAL_TEST_REF_REJECT',
    ]);

    $response = $this->actingAs($userBarber)->postJson("/api/v1/barber/bookings/{$appointment->id}/verify-payment", [
        'action' => 'reject',
        'reason' => 'Receipt illegible and fake reference',
    ]);

    $response->assertStatus(200);

    // Appointment is cancelled and cancellation reason stored
    expect($appointment->fresh()->status->value)->toBe(AppointmentStatus::cancelled->value);
    expect($appointment->fresh()->cancellation_reason)->toBe('Receipt illegible and fake reference');
    expect((bool) $appointment->fresh()->deposit_paid)->toBeFalse();

    // Payment marked failed
    expect($payment->fresh()->status->value)->toBe('failed');

    // Status history entry exists
    $this->assertDatabaseHas('appointment_status_history', [
        'appointment_id' => $appointment->id,
        'new_status' => AppointmentStatus::cancelled->value,
        'changed_by_user_id' => $userBarber->id,
        'reason' => 'Receipt illegible and fake reference',
    ]);

    // Audit log entry exists
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $userBarber->id,
        'action' => 'payment.rejected',
        'target_id' => $appointment->id,
    ]);
});
