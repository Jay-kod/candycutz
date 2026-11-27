<?php

declare(strict_types=1);

use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Branch;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\RefundRequest;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Mail::fake();
    Storage::fake('local');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function createPaidAppointmentForRefund(
    User $customer,
    Barber $barber,
    Branch $branch,
    Service $service,
    Carbon $startAt,
    int $amountNaira = 10000,
    string $provider = 'paystack',
): array {
    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'branch_id' => $branch->id,
        'service_id' => $service->id,
        'appointment_date' => $startAt->toDateString(),
        'appointment_time' => $startAt->format('H:i'),
        'end_time' => $startAt->copy()->addMinutes(30)->format('H:i'),
        'status' => AppointmentStatus::confirmed->value,
        'payment_status' => AppointmentPaymentStatus::paid->value,
        'deposit_paid' => true,
        'deposit_amount' => $amountNaira,
        'total_price' => $amountNaira,
        'grand_total' => $amountNaira,
    ]);

    $payment = Payment::factory()->create([
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'amount' => $amountNaira,
        'amount_minor' => $amountNaira * 100,
        'refunded_minor' => 0,
        'provider' => $provider,
        'payment_method' => $provider,
        'status' => PaymentStatus::verified->value,
        'paid_at' => now(),
    ]);

    return [$appointment, $payment];
}

it('allows admin to configure refund policy and enforces role guards', function (): void {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();
    $barber = User::factory()->barber()->create();

    $this->actingAs($customer)
        ->postJson('/api/v1/admin/refunds/policy', [
            'refund_full_hours' => 48,
            'refund_partial_hours' => 12,
            'refund_partial_percent' => 50,
            'refund_compassionate_enabled' => true,
            'refund_sla_hours' => 24,
        ])
        ->assertForbidden();

    $this->actingAs($barber)
        ->getJson('/api/v1/admin/refunds')
        ->assertForbidden();

    $res = $this->actingAs($admin)
        ->postJson('/api/v1/admin/refunds/policy', [
            'refund_full_hours' => 48,
            'refund_partial_hours' => 12,
            'refund_partial_percent' => 60,
            'refund_compassionate_enabled' => true,
            'refund_sla_hours' => 24,
            'refund_require_evidence_for_compassionate' => true,
        ])
        ->assertOk();

    expect($res->json('data.refund_full_hours'))->toBe(48);
    expect($res->json('data.refund_partial_percent'))->toBe(60);
    expect(Setting::where('key', 'refund_partial_percent')->value('value'))->toBe('60');
});

it('processes 100% auto refund when cancelled 24+ hours ahead, 50% at 6-24h, and 0% inside 6h', function (): void {
    Carbon::setTestNow(Carbon::create(2026, 11, 26, 10, 0, 0, 'Africa/Lagos'));

    $customer = User::factory()->customer()->create();
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $branch = Branch::factory()->create();
    $service = Service::factory()->create();

    // Case 1: 30 hours ahead -> 100% automatic Paystack refund
    [$apptFull, $payFull] = createPaidAppointmentForRefund($customer, $barber, $branch, $service, Carbon::create(2026, 11, 27, 16, 0, 0, 'Africa/Lagos'), 10000);

    $this->actingAs($customer)
        ->getJson("/api/v1/appointments/{$apptFull->id}/refund-eligibility")
        ->assertOk()
        ->assertJsonPath('data.percentage', 100)
        ->assertJsonPath('data.refund_amount', 10000);

    $resFull = $this->actingAs($customer)
        ->patchJson("/api/v1/appointments/{$apptFull->id}/cancel", [
            'reason_category' => 'changed_mind',
            'reason' => 'Rescheduling next week',
        ])
        ->assertOk();

    expect($resFull->json('data.status'))->toBe('cancelled');
    expect($resFull->json('data.refund_request.status'))->toBe('completed');
    expect($resFull->json('data.refund_request.refund_percentage'))->toBe(100);
    expect($payFull->fresh()->status)->toBe(PaymentStatus::refunded);
    expect($payFull->fresh()->refunded_minor)->toBe(1000000);
    expect(PaymentTransaction::where('payment_id', $payFull->id)->where('transaction_type', 'refund')->exists())->toBeTrue();
    expect(Notification::where('recipient_id', $customer->id)->where('type', 'refund_completed')->exists())->toBeTrue();

    // Case 2: 12 hours ahead -> 50% automatic partial refund
    [$apptPartial, $payPartial] = createPaidAppointmentForRefund($customer, $barber, $branch, $service, Carbon::create(2026, 11, 26, 22, 0, 0, 'Africa/Lagos'), 10000);

    $resPartial = $this->actingAs($customer)
        ->patchJson("/api/v1/appointments/{$apptPartial->id}/cancel", [
            'reason_category' => 'changed_mind',
            'reason' => 'Conflict arose',
        ])
        ->assertOk();

    expect($resPartial->json('data.refund_request.status'))->toBe('completed');
    expect($resPartial->json('data.refund_request.refund_percentage'))->toBe(50);
    expect($resPartial->json('data.refund_request.refund_amount'))->toBe(5000);
    expect($payPartial->fresh()->status)->toBe(PaymentStatus::partially_refunded);
    expect($payPartial->fresh()->refunded_minor)->toBe(500000);

    // Case 3: 2 hours ahead -> 0% automatic refund
    [$apptLate, $payLate] = createPaidAppointmentForRefund($customer, $barber, $branch, $service, Carbon::create(2026, 11, 26, 12, 0, 0, 'Africa/Lagos'), 10000);

    $resLate = $this->actingAs($customer)
        ->patchJson("/api/v1/appointments/{$apptLate->id}/cancel", [
            'reason_category' => 'changed_mind',
            'reason' => 'Too late notice',
        ])
        ->assertOk();

    expect($resLate->json('data.status'))->toBe('cancelled');
    expect($resLate->json('data.refund_request'))->toBeNull();
    expect(RefundRequest::where('appointment_id', $apptLate->id)->exists())->toBeFalse();
    expect($payLate->fresh()->status)->toBe(PaymentStatus::verified);
});

it('supports compassionate refund with private evidence, IDOR guard, manual transfer completion, and duplicate check', function (): void {
    Carbon::setTestNow(Carbon::create(2026, 11, 26, 10, 0, 0, 'Africa/Lagos'));

    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();
    $otherCustomer = User::factory()->customer()->create();
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $branch = Branch::factory()->create();
    $service = Service::factory()->create();

    [$appointment, $payment] = createPaidAppointmentForRefund(
        $customer,
        $barber,
        $branch,
        $service,
        Carbon::create(2026, 11, 26, 11, 30, 0, 'Africa/Lagos'),
        8000,
        'manual_transfer'
    );

    $evidence = UploadedFile::fake()->create('doctor-note.pdf', 120, 'application/pdf');

    $cancelRes = $this->actingAs($customer)
        ->post("/api/v1/appointments/{$appointment->id}/cancel", [
            '_method' => 'PATCH',
            'reason_category' => 'illness',
            'reason' => 'Sudden fever overnight',
            'request_compassionate' => 1,
            'evidence' => $evidence,
        ], ['Accept' => 'application/json'])
        ->assertOk();

    $refundId = $cancelRes->json('data.refund_request.id');
    expect($refundId)->not->toBeNull();
    expect($cancelRes->json('data.refund_request.status'))->toBe('pending');
    expect($cancelRes->json('data.refund_request.type'))->toBe('compassionate');
    expect($appointment->fresh()->payment_status)->toBe(AppointmentPaymentStatus::refund_pending);

    $storedPath = RefundRequest::findOrFail($refundId)->supporting_evidence;
    expect($storedPath)->not->toBeNull();
    Storage::disk('local')->assertExists($storedPath);

    // IDOR guard on evidence download
    $this->actingAs($otherCustomer)->get("/api/v1/refunds/{$refundId}/evidence")->assertForbidden();
    $this->actingAs($customer)->get("/api/v1/refunds/{$refundId}/evidence")->assertOk();
    $this->actingAs($admin)->get("/api/v1/refunds/{$refundId}/evidence")->assertOk();

    // Duplicate refund submission blocked with 422
    $this->actingAs($customer)
        ->postJson('/api/v1/refunds', [
            'appointment_id' => $appointment->id,
            'reason_category' => 'illness',
            'reason_text' => 'Duplicate try',
            'is_compassionate' => true,
        ])
        ->assertStatus(422);

    // Admin approves manual_transfer compassionate refund -> enters 'processing'
    $approveRes = $this->actingAs($admin)
        ->postJson("/api/v1/admin/refunds/{$refundId}/decide", [
            'decision' => 'approve',
            'note' => 'Verified medical note, initiating bank transfer.',
        ])
        ->assertOk();

    expect($approveRes->json('data.status'))->toBe('processing');
    expect(Notification::where('recipient_id', $customer->id)->where('type', 'refund_approved')->exists())->toBeTrue();

    // Admin marks manual bank transfer completed -> 'completed'
    $completeRes = $this->actingAs($admin)
        ->postJson("/api/v1/admin/refunds/{$refundId}/complete-manual", [
            'transfer_reference' => 'NIP-99887766',
            'note' => 'Sent via Zenith Bank',
        ])
        ->assertOk();

    expect($completeRes->json('data.status'))->toBe('completed');
    expect($completeRes->json('data.gateway_refund_ref'))->toBe('NIP-99887766');
    expect($payment->fresh()->status)->toBe(PaymentStatus::refunded);
    expect($appointment->fresh()->payment_status)->toBe(AppointmentPaymentStatus::refunded);
});

it('allows admin to reject a compassionate refund request with note', function (): void {
    Carbon::setTestNow(Carbon::create(2026, 11, 26, 10, 0, 0, 'Africa/Lagos'));

    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $branch = Branch::factory()->create();
    $service = Service::factory()->create();

    [$appointment] = createPaidAppointmentForRefund(
        $customer,
        $barber,
        $branch,
        $service,
        Carbon::create(2026, 11, 26, 11, 0, 0, 'Africa/Lagos'),
        5000,
        'paystack'
    );

    $this->actingAs($customer)->patchJson("/api/v1/appointments/{$appointment->id}/cancel", [
        'reason_category' => 'changed_mind',
    ])->assertOk();

    $createRes = $this->actingAs($customer)
        ->postJson('/api/v1/refunds', [
            'appointment_id' => $appointment->id,
            'reason_category' => 'emergency',
            'reason_text' => 'Emergency happened right before appointment',
            'is_compassionate' => true,
        ])
        ->assertStatus(201);

    $refundId = $createRes->json('data.id');

    $rejectRes = $this->actingAs($admin)
        ->postJson("/api/v1/admin/refunds/{$refundId}/decide", [
            'decision' => 'reject',
            'note' => 'Insufficient verification provided',
        ])
        ->assertOk();

    expect($rejectRes->json('data.status'))->toBe('rejected');
    expect(Notification::where('recipient_id', $customer->id)->where('type', 'refund_rejected')->exists())->toBeTrue();

    // Already-decided request rejects a second concurrent/duplicate decision cleanly
    $this->actingAs($admin)
        ->postJson("/api/v1/admin/refunds/{$refundId}/decide", [
            'decision' => 'approve',
        ])
        ->assertStatus(422);
});

it('blocks standard refund claims after deposit forfeiture and syncs no-show refunds into the unified ledger', function (): void {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $branch = Branch::factory()->create();
    $service = Service::factory()->create(['price' => 6000]);

    [$appointment] = createPaidAppointmentForRefund(
        $customer,
        $barber,
        $branch,
        $service,
        Carbon::create(2026, 11, 26, 11, 0, 0, 'Africa/Lagos'),
        6000,
        'paystack'
    );

    $this->actingAs($barberUser)
        ->patchJson("/api/v1/barber/appointments/{$appointment->id}/no-show")
        ->assertOk();

    $forfeitRes = $this->actingAs($barberUser)
        ->postJson("/api/v1/barber/appointments/{$appointment->id}/forfeit-deposit", ['reason' => 'No arrival'])
        ->assertOk();

    $recordId = $forfeitRes->json('data.id');

    // Customer cannot bypass forfeited deposit via standard /refunds endpoint
    $this->actingAs($customer)
        ->postJson('/api/v1/refunds', [
            'appointment_id' => $appointment->id,
            'reason_category' => 'illness',
            'is_compassionate' => true,
        ])
        ->assertStatus(422);

    // Admin no-show refund syncs to unified refund_requests ledger
    $this->actingAs($admin)
        ->postJson("/api/v1/admin/no-shows/{$recordId}/initiate-refund", [
            'reason' => 'Goodwill override',
        ])
        ->assertOk();

    expect(RefundRequest::where('appointment_id', $appointment->id)->where('type', 'no_show_dispute')->where('status', 'completed')->exists())->toBeTrue();
    $this->actingAs($customer)->getJson('/api/v1/customer/refunds')->assertOk()->assertJsonPath('data.items.0.type', 'no_show_dispute');
});
