<?php

declare(strict_types=1);

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Payment;
use App\Models\PaymentTransaction;
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
                'reference' => 'PAYSTACK_MOCK_REF_123',
            ],
        ], 200),
        'https://api.paystack.co/transaction/verify/*' => Http::response([
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'status' => 'success',
                'reference' => 'PAYSTACK_MOCK_REF_123',
                'amount' => 500000,
                'id' => 12345678,
            ],
        ], 200),
    ]);
});

it('initiates paystack checkout and returns authorization details', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $barber = Barber::factory()->create();
    $service = Service::factory()->create(['price' => 500000]); // 5,000 NGN in kobo

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'grand_total' => 500000,
        'total_price' => 500000,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($customer)->postJson('/api/v1/payments/checkout', [
        'appointment_id' => $appointment->id,
        'payment_method' => 'paystack',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => [
            'checkout' => [
                'reference',
                'gateway',
                'amount_kobo',
                'authorization_url',
            ],
        ],
    ]);

    $this->assertDatabaseHas('payments', [
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'payment_method' => 'paystack',
        'status' => 'pending',
        'amount' => 500000,
    ]);
});

it('processes paystack webhook with valid signature and confirms appointment idempotently', function () {
    $secretKey = 'sk_test_mock_secret_key_12345';
    $customer = User::factory()->create(['role' => 'customer']);
    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'pending',
        'grand_total' => 350000,
    ]);

    $payment = Payment::create([
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'amount' => 350000,
        'currency' => 'NGN',
        'status' => 'pending',
        'payment_method' => 'paystack',
        'transaction_ref' => 'TXN_TEST_REF_001',
        'gateway_reference' => 'PAYSTACK_REF_001',
    ]);

    $payload = [
        'event' => 'charge.success',
        'data' => [
            'id' => 987654321,
            'reference' => 'PAYSTACK_REF_001',
            'amount' => 350000,
            'currency' => 'NGN',
            'status' => 'success',
            'metadata' => [
                'appointment_id' => $appointment->id,
                'customer_id' => $customer->id,
            ],
        ],
    ];

    $rawContent = json_encode($payload);
    $signature = hash_hmac('sha512', $rawContent, $secretKey);

    // 1. First webhook delivery
    $response = $this->call(
        'POST',
        '/api/v1/payments/webhook',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
        ],
        $rawContent
    );

    $response->assertStatus(200);
    $response->assertJson([
        'received' => true,
        'event_id' => '987654321',
        'type' => 'charge.success',
    ]);

    // Verify database state: payment is successful, appointment confirmed, deposit paid
    expect($payment->fresh()->status)->toBe('successful');
    expect($appointment->fresh()->status->value)->toBe('confirmed');
    expect((bool) $appointment->fresh()->deposit_paid)->toBeTrue();

    // Verify transaction record created
    expect(PaymentTransaction::where('gateway_event_id', '987654321')->count())->toBe(1);

    // 2. Second webhook delivery (idempotency check)
    $response2 = $this->call(
        'POST',
        '/api/v1/payments/webhook',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
        ],
        $rawContent
    );

    $response2->assertStatus(200);
    // Should NOT create duplicate transactions
    expect(PaymentTransaction::where('gateway_event_id', '987654321')->count())->toBe(1);
    expect($payment->fresh()->status)->toBe('successful');
});

it('rejects paystack webhook with invalid signature', function () {
    $rawContent = json_encode(['event' => 'charge.success', 'data' => []]);

    $response = $this->call(
        'POST',
        '/api/v1/payments/webhook',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_PAYSTACK_SIGNATURE' => 'invalid_signature_hash',
        ],
        $rawContent
    );

    $response->assertStatus(400);
    $response->assertJsonStructure(['error']);
});
