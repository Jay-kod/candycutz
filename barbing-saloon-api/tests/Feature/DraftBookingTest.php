<?php

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Branch;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;

it('requires authentication for draft endpoints', function () {
    $this->getJson('/api/v1/appointments/drafts')->assertUnauthorized();
    $this->postJson('/api/v1/appointments/draft', [])->assertUnauthorized();
    $this->postJson('/api/v1/appointments/drafts/1/resume')->assertUnauthorized();
    $this->deleteJson('/api/v1/appointments/drafts/1')->assertUnauthorized();
});

it('lists only draft appointments for the authenticated customer', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $otherCustomer = User::factory()->create(['role' => 'customer']);

    $draft = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'status' => AppointmentStatus::draft->value,
        'draft_expires_at' => now()->addMinutes(15),
        'draft_step' => 'payment',
    ]);

    $confirmed = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'status' => AppointmentStatus::confirmed->value,
    ]);

    $otherDraft = Appointment::factory()->create([
        'customer_id' => $otherCustomer->id,
        'status' => AppointmentStatus::draft->value,
        'draft_expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->actingAs($customer)->getJson('/api/v1/appointments/drafts');
    $response->assertOk();

    $ids = collect($response->json('data'))->pluck('id')->all();
    expect($ids)->toContain($draft->id);
    expect($ids)->not->toContain($confirmed->id);
    expect($ids)->not->toContain($otherDraft->id);
});

it('allows customer to discard their draft booking and releases slot', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $draft = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'status' => AppointmentStatus::draft->value,
        'draft_expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->actingAs($customer)->deleteJson("/api/v1/appointments/drafts/{$draft->id}");
    $response->assertOk();

    $draft->refresh();
    expect($draft->status)->toBe(AppointmentStatus::cancelled);
    expect($draft->draft_expires_at)->toBeNull();
});

it('prevents another customer from discarding someone elses draft (IDOR protection)', function () {
    $owner = User::factory()->create(['role' => 'customer']);
    $attacker = User::factory()->create(['role' => 'customer']);

    $draft = Appointment::factory()->create([
        'customer_id' => $owner->id,
        'status' => AppointmentStatus::draft->value,
        'draft_expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->actingAs($attacker)->deleteJson("/api/v1/appointments/drafts/{$draft->id}");
    $response->assertForbidden();

    $draft->refresh();
    expect($draft->status)->toBe(AppointmentStatus::draft);
});

it('allows customer to resume active draft and renews 15-minute hold', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $draft = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'status' => AppointmentStatus::draft->value,
        'draft_expires_at' => now()->addMinutes(5),
    ]);

    $response = $this->actingAs($customer)->postJson("/api/v1/appointments/drafts/{$draft->id}/resume");
    $response->assertOk();

    $draft->refresh();
    expect($draft->draft_expires_at->greaterThan(now()->addMinutes(10)))->toBeTrue();
});

it('releases expired drafts via bookings:release-unpaid artisan command', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $expiredDraft = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'status' => AppointmentStatus::draft->value,
        'draft_expires_at' => now()->subMinutes(5),
    ]);

    $activeDraft = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'status' => AppointmentStatus::draft->value,
        'draft_expires_at' => now()->addMinutes(10),
    ]);

    $this->artisan('bookings:release-unpaid')
        ->assertSuccessful();

    $expiredDraft->refresh();
    $activeDraft->refresh();

    expect($expiredDraft->status)->toBe(AppointmentStatus::cancelled);
    expect($expiredDraft->draft_expires_at)->toBeNull();

    expect($activeDraft->status)->toBe(AppointmentStatus::draft);
    expect($activeDraft->draft_expires_at->isFuture())->toBeTrue();
});
