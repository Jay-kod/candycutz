<?php

declare(strict_types=1);

use App\Domain\Payment\Actions\VerifyReceipt;
use App\Domain\Payment\Enums\ManualTransferState;
use App\Domain\Payment\Services\ManualTransferStateMachine;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Storage::fake('local');
});

it('initiates manual transfer checkout and creates payment in awaiting_transfer status', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $barber = Barber::factory()->create();
    $service = Service::factory()->create(['price' => 450000]);

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'grand_total' => 450000,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($customer)->postJson('/api/v1/payments/checkout', [
        'appointment_id' => $appointment->id,
        'payment_method' => 'manual_transfer',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('data.checkout.gateway', 'manual_transfer');
    $response->assertJsonStructure([
        'data' => [
            'checkout' => [
                'reference',
                'gateway',
                'amount_kobo',
                'meta' => [
                    'bank_name',
                    'account_name',
                    'account_number',
                    'instructions',
                ],
            ],
        ],
    ]);

    $payment = Payment::where('appointment_id', $appointment->id)->first();
    expect($payment)->not->toBeNull();
    expect($payment->status)->toBe('awaiting_transfer');
    expect($payment->payment_method)->toBe('manual_transfer');
});

it('progresses receipt upload through state machine to under_review with SLA tracking', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'pending',
        'grand_total' => 300000,
    ]);

    $file = UploadedFile::fake()->create('bank_receipt.pdf', 1024, 'application/pdf');

    $response = $this->actingAs($customer)->postJson("/api/v1/payments/appointments/{$appointment->id}/receipt", [
        'receipt' => $file,
    ]);

    $response->assertStatus(200);

    $payment = Payment::where('appointment_id', $appointment->id)->first();
    expect($payment->status)->toBe('under_review');
    expect($payment->receipt_url)->not->toBeNull();
    expect($payment->receipt_uploaded_at)->not->toBeNull();
    expect($payment->sla_expires_at)->not->toBeNull();

    // Verify storage file exists on private disk
    Storage::disk('local')->assertExists($payment->receipt_url);

    // Verify audit transactions were recorded for transitions
    $transitions = PaymentTransaction::where('payment_id', $payment->id)->get();
    expect($transitions->count())->toBeGreaterThanOrEqual(2);
});

it('verifies receipt approving payment, confirming appointment, and logging audit trail', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);
    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'pending',
        'grand_total' => 400000,
    ]);

    $payment = Payment::create([
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'amount' => 400000,
        'currency' => 'NGN',
        'status' => 'under_review',
        'payment_method' => 'manual_transfer',
        'transaction_ref' => 'MANUAL_TEST_002',
        'receipt_url' => 'receipts/test.png',
        'receipt_uploaded_at' => now(),
        'sla_expires_at' => now()->addHours(24),
    ]);

    $verifyAction = app(VerifyReceipt::class);
    $verifiedPayment = $verifyAction->execute($payment, approve: true, reason: 'Payment confirmed in bank statement', actorId: $admin->id);

    expect($verifiedPayment->status)->toBe('successful');
    expect($verifiedPayment->verified_by_user_id)->toBe($admin->id);
    expect($verifiedPayment->verified_at)->not->toBeNull();

    // Appointment confirmed
    expect($appointment->fresh()->status->value)->toBe('confirmed');
    expect((bool) $appointment->fresh()->deposit_paid)->toBeTrue();

    // Audit log
    $lastTxn = PaymentTransaction::where('payment_id', $payment->id)
        ->where('transaction_type', 'capture')
        ->first();

    expect($lastTxn)->not->toBeNull();
    expect($lastTxn->status)->toBe('verified');
});

it('rejects receipt and records reason in audit trail', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);
    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'pending',
        'grand_total' => 400000,
    ]);

    $payment = Payment::create([
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'amount' => 400000,
        'currency' => 'NGN',
        'status' => 'under_review',
        'payment_method' => 'manual_transfer',
        'transaction_ref' => 'MANUAL_TEST_003',
        'receipt_url' => 'receipts/fake.jpg',
    ]);

    $verifyAction = app(VerifyReceipt::class);
    $rejectedPayment = $verifyAction->execute($payment, approve: false, reason: 'Unclear transaction reference on image', actorId: $admin->id);

    expect($rejectedPayment->status)->toBe('failed');
    expect($rejectedPayment->error_message)->toBe('Unclear transaction reference on image');
    expect($rejectedPayment->verified_by_user_id)->toBe($admin->id);

    $lastTxn = PaymentTransaction::where('payment_id', $payment->id)
        ->where('transaction_type', 'void')
        ->first();

    expect($lastTxn)->not->toBeNull();
    expect($lastTxn->status)->toBe('rejected');
});

it('guards against illegal state transitions in state machine', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $appointment = Appointment::factory()->create(['customer_id' => $customer->id]);

    $payment = Payment::create([
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'amount' => 200000,
        'currency' => 'NGN',
        'status' => 'awaiting_transfer',
        'payment_method' => 'manual_transfer',
        'transaction_ref' => 'MANUAL_ILLEGAL_001',
    ]);

    $stateMachine = app(ManualTransferStateMachine::class);

    // Direct transition from awaiting_transfer to verified without uploading receipt is forbidden
    expect(fn () => $stateMachine->transition($payment, ManualTransferState::verified))
        ->toThrow(ValidationException::class);
});
