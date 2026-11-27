<?php

use App\Domain\Booking\Actions\CreateDraftBooking;
use App\Domain\Booking\Actions\DiscardDraftBooking;
use App\Domain\Booking\Actions\ResumeDraftBooking;
use App\Domain\Booking\DataObjects\BookingData;
use App\Domain\Payment\Services\PaymentConfirmer;
use App\Domain\Shared\Enums\AppointmentSource;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Exceptions\BookingSlotUnavailableException;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    // Setup standard branch, barber, working hours, service
    $this->branch = Branch::first() ?? Branch::factory()->create();
    $this->barberUser = User::factory()->create(['role' => 'barber', 'is_active' => true]);
    $this->barber = Barber::factory()->create([
        'user_id' => $this->barberUser->id,
        'branch_id' => $this->branch->id,
    ]);

    // Open every day from 08:00 to 20:00
    for ($d = 0; $d <= 6; $d++) {
        WorkingHour::create([
            'barber_id' => $this->barber->id,
            'day_of_week' => $d,
            'open_time' => '08:00:00',
            'close_time' => '20:00:00',
            'is_closed' => false,
        ]);
    }

    $this->service = Service::factory()->create([
        'price' => 5000,
        'duration_minutes' => 30,
        'is_active' => true,
    ]);

    $this->customer1 = User::factory()->create(['role' => 'customer']);
    $this->customer2 = User::factory()->create(['role' => 'customer']);
});

it('prevents two customers from creating drafts for the same slot (pessimistic slot collision)', function () {
    $date = Carbon::tomorrow()->toDateString();
    $time = '10:00';

    $payload = [
        'barber_id' => $this->barber->id,
        'service_id' => $this->service->id,
        'appointment_date' => $date,
        'appointment_time' => $time,
    ];

    // Customer 1 creates draft
    $response1 = $this->actingAs($this->customer1)->postJson('/api/v1/appointments/draft', $payload);
    $response1->assertCreated();
    expect($response1->json('data.status'))->toBe(AppointmentStatus::draft->value);

    // Customer 2 attempts to create draft on same slot
    $response2 = $this->actingAs($this->customer2)->postJson('/api/v1/appointments/draft', $payload);
    $response2->assertStatus(409);
    expect($response2->json('error.code'))->toBe('BOOKING_SLOT_UNAVAILABLE');
});

it('immediately frees slot for another customer when draft is discarded', function () {
    $date = Carbon::tomorrow()->toDateString();
    $time = '11:00';

    $payload = [
        'barber_id' => $this->barber->id,
        'service_id' => $this->service->id,
        'appointment_date' => $date,
        'appointment_time' => $time,
    ];

    // Customer 1 creates draft
    $response1 = $this->actingAs($this->customer1)->postJson('/api/v1/appointments/draft', $payload);
    $response1->assertCreated();
    $draftId = $response1->json('data.id');

    // Customer 1 discards draft
    $discardResponse = $this->actingAs($this->customer1)->deleteJson("/api/v1/appointments/drafts/{$draftId}");
    $discardResponse->assertOk();

    // Customer 2 now attempts to book the same slot
    $response2 = $this->actingAs($this->customer2)->postJson('/api/v1/appointments/draft', $payload);
    $response2->assertCreated();
    expect($response2->json('data.id'))->not->toBe($draftId);
    expect($response2->json('data.status'))->toBe(AppointmentStatus::draft->value);
});

it('automatically supersedes previous drafts by the same customer (anti-hoarding protection)', function () {
    $date = Carbon::tomorrow()->toDateString();

    // Customer 1 books slot 1 at 12:00
    $res1 = $this->actingAs($this->customer1)->postJson('/api/v1/appointments/draft', [
        'barber_id' => $this->barber->id,
        'service_id' => $this->service->id,
        'appointment_date' => $date,
        'appointment_time' => '12:00',
    ]);
    $res1->assertCreated();
    $draft1Id = $res1->json('data.id');

    // Customer 1 changes mind and books slot 2 at 13:00 without manually discarding
    $res2 = $this->actingAs($this->customer1)->postJson('/api/v1/appointments/draft', [
        'barber_id' => $this->barber->id,
        'service_id' => $this->service->id,
        'appointment_date' => $date,
        'appointment_time' => '13:00',
    ]);
    $res2->assertCreated();
    $draft2Id = $res2->json('data.id');

    // Verify slot 1 was superseded/cancelled
    $draft1 = Appointment::find($draft1Id);
    expect($draft1->status)->toBe(AppointmentStatus::cancelled);
    expect($draft1->cancellation_reason)->toContain('Superseded');

    // Verify Customer 2 can now book slot 1 at 12:00
    $resCustomer2 = $this->actingAs($this->customer2)->postJson('/api/v1/appointments/draft', [
        'barber_id' => $this->barber->id,
        'service_id' => $this->service->id,
        'appointment_date' => $date,
        'appointment_time' => '12:00',
    ]);
    $resCustomer2->assertCreated();
});

it('allows customer to resume expired draft if slot is still free', function () {
    $date = Carbon::tomorrow()->toDateString();
    $time = '14:00';

    $draft = Appointment::factory()->create([
        'customer_id' => $this->customer1->id,
        'barber_id' => $this->barber->id,
        'service_id' => $this->service->id,
        'branch_id' => $this->branch->id,
        'appointment_date' => $date,
        'appointment_time' => $time,
        'end_time' => '14:30',
        'status' => AppointmentStatus::draft->value,
        'draft_expires_at' => now()->subMinutes(10), // Expired
    ]);

    // Resume draft
    $response = $this->actingAs($this->customer1)->postJson("/api/v1/appointments/drafts/{$draft->id}/resume");
    $response->assertOk();

    $draft->refresh();
    expect($draft->draft_expires_at->isFuture())->toBeTrue();
});

it('rejects resuming expired draft if another customer has booked that slot', function () {
    $date = Carbon::tomorrow()->toDateString();
    $time = '15:00';

    $expiredDraft = Appointment::factory()->create([
        'customer_id' => $this->customer1->id,
        'barber_id' => $this->barber->id,
        'service_id' => $this->service->id,
        'branch_id' => $this->branch->id,
        'appointment_date' => $date,
        'appointment_time' => $time,
        'end_time' => '15:30',
        'status' => AppointmentStatus::cancelled->value,
        'cancellation_reason' => 'Draft slot hold expired without payment.',
        'draft_expires_at' => now()->subMinutes(10), // Expired
    ]);

    // Customer 2 legitimately books that slot in the meantime
    Appointment::factory()->create([
        'customer_id' => $this->customer2->id,
        'barber_id' => $this->barber->id,
        'service_id' => $this->service->id,
        'branch_id' => $this->branch->id,
        'appointment_date' => $date,
        'appointment_time' => $time,
        'end_time' => '15:30',
        'status' => AppointmentStatus::confirmed->value,
    ]);

    // Customer 1 tries to resume expired draft
    $response = $this->actingAs($this->customer1)->postJson("/api/v1/appointments/drafts/{$expiredDraft->id}/resume");
    $response->assertStatus(409);
    expect($response->json('error.code'))->toBe('BOOKING_SLOT_UNAVAILABLE');
});

it('confirms draft appointment and locks permanently when payment succeeds idempotently', function () {
    $date = Carbon::tomorrow()->toDateString();

    $draft = Appointment::factory()->create([
        'customer_id' => $this->customer1->id,
        'barber_id' => $this->barber->id,
        'service_id' => $this->service->id,
        'branch_id' => $this->branch->id,
        'appointment_date' => $date,
        'appointment_time' => '16:00',
        'end_time' => '16:30',
        'status' => AppointmentStatus::draft->value,
        'total_price' => 5000,
        'grand_total' => 5000,
        'draft_expires_at' => now()->addMinutes(15),
    ]);

    $ref = 'PAY_TEST_'.uniqid();
    $payment = Payment::create([
        'appointment_id' => $draft->id,
        'customer_id' => $this->customer1->id,
        'amount' => 5000,
        'currency' => 'NGN',
        'status' => 'pending',
        'payment_method' => 'paystack',
        'reference' => $ref,
        'gateway_reference' => $ref,
    ]);

    $confirmer = app(PaymentConfirmer::class);
    $confirmer->confirm($ref, 'webhook', ['amount' => 500000]); // 5000 NGN in kobo

    $draft->refresh();
    expect($draft->status)->toBe(AppointmentStatus::confirmed);
    expect($draft->deposit_paid)->toBeTrue();
    expect($draft->draft_expires_at)->toBeNull();

    // Idempotent secondary call
    $confirmer->confirm($ref, 'webhook', ['amount' => 500000]);
    $draft->refresh();
    expect($draft->status)->toBe(AppointmentStatus::confirmed);
});

it('cleans up stale expired drafts on-the-fly when a new customer books without waiting for cron', function () {
    $date = Carbon::tomorrow()->toDateString();
    $time = '17:00';

    // Customer 1 created draft that expired 5 minutes ago, but cron has not run yet
    $expiredDraft = Appointment::factory()->create([
        'customer_id' => $this->customer1->id,
        'barber_id' => $this->barber->id,
        'service_id' => $this->service->id,
        'branch_id' => $this->branch->id,
        'appointment_date' => $date,
        'appointment_time' => $time,
        'end_time' => '17:30',
        'status' => AppointmentStatus::draft->value,
        'draft_expires_at' => now()->subMinutes(5),
    ]);

    // Customer 2 comes in to book that slot immediately
    $response = $this->actingAs($this->customer2)->postJson('/api/v1/appointments/draft', [
        'barber_id' => $this->barber->id,
        'service_id' => $this->service->id,
        'appointment_date' => $date,
        'appointment_time' => $time,
    ]);

    $response->assertCreated();
    expect($response->json('data.id'))->not->toBe($expiredDraft->id);

    // Verify expired draft was marked cancelled on-the-fly
    $expiredDraft->refresh();
    expect($expiredDraft->status)->toBe(AppointmentStatus::cancelled);
    expect($expiredDraft->cancellation_reason)->toContain('expired');
});

it('revives expired draft appointment to confirmed when late webhook payment arrives and slot is free', function () {
    $date = Carbon::tomorrow()->toDateString();
    $time = '18:00';

    // Draft expired and was cancelled by sweeper
    $expiredDraft = Appointment::factory()->create([
        'customer_id' => $this->customer1->id,
        'barber_id' => $this->barber->id,
        'service_id' => $this->service->id,
        'branch_id' => $this->branch->id,
        'appointment_date' => $date,
        'appointment_time' => $time,
        'end_time' => '18:30',
        'status' => AppointmentStatus::cancelled->value,
        'cancellation_reason' => 'Draft slot hold expired without payment.',
        'draft_expires_at' => null,
        'total_price' => 5000,
        'grand_total' => 5000,
    ]);

    $ref = 'PAY_LATE_'.uniqid();
    Payment::create([
        'appointment_id' => $expiredDraft->id,
        'customer_id' => $this->customer1->id,
        'amount' => 5000,
        'currency' => 'NGN',
        'status' => 'pending',
        'payment_method' => 'paystack',
        'reference' => $ref,
        'gateway_reference' => $ref,
    ]);

    $confirmer = app(PaymentConfirmer::class);
    $confirmer->confirm($ref, 'webhook', ['amount' => 500000]);

    $expiredDraft->refresh();
    expect($expiredDraft->status)->toBe(AppointmentStatus::confirmed);
    expect($expiredDraft->deposit_paid)->toBeTrue();
    expect($expiredDraft->cancellation_reason)->toBeNull();
});

it('safely records payment and flags appointment if payment arrives for expired draft whose slot was re-booked', function () {
    $date = Carbon::tomorrow()->toDateString();
    $time = '19:00';

    // Draft 1 expired and was cancelled
    $expiredDraft = Appointment::factory()->create([
        'customer_id' => $this->customer1->id,
        'barber_id' => $this->barber->id,
        'service_id' => $this->service->id,
        'branch_id' => $this->branch->id,
        'appointment_date' => $date,
        'appointment_time' => $time,
        'end_time' => '19:30',
        'status' => AppointmentStatus::cancelled->value,
        'cancellation_reason' => 'Draft slot hold expired without payment.',
        'draft_expires_at' => null,
        'total_price' => 5000,
        'grand_total' => 5000,
    ]);

    // Customer 2 booked that slot in the interim
    Appointment::factory()->create([
        'customer_id' => $this->customer2->id,
        'barber_id' => $this->barber->id,
        'service_id' => $this->service->id,
        'branch_id' => $this->branch->id,
        'appointment_date' => $date,
        'appointment_time' => $time,
        'end_time' => '19:30',
        'status' => AppointmentStatus::confirmed->value,
    ]);

    $ref = 'PAY_CONFLICT_'.uniqid();
    $payment = Payment::create([
        'appointment_id' => $expiredDraft->id,
        'customer_id' => $this->customer1->id,
        'amount' => 5000,
        'currency' => 'NGN',
        'status' => 'pending',
        'payment_method' => 'paystack',
        'reference' => $ref,
        'gateway_reference' => $ref,
    ]);

    $confirmer = app(PaymentConfirmer::class);
    $confirmer->confirm($ref, 'webhook', ['amount' => 500000]);

    $payment->refresh();
    expect($payment->status->value)->toBe('successful');

    $expiredDraft->refresh();
    // Appointment remains cancelled because slot was taken, but flagged with alert notes for staff reassignment
    expect($expiredDraft->status)->toBe(AppointmentStatus::cancelled);
    expect($expiredDraft->notes)->toContain('CRITICAL');
});
