<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Shared\Enums\AppointmentSource;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('completes a confirmed online appointment when given its generated CC-XXXXXX verification code', function () {
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $customer = User::factory()->customer()->create();
    $service = Service::factory()->create();

    $appointment = Appointment::factory()->create([
        'booking_reference' => 'CC-ESNNSR',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::confirmed->value,
        'source' => AppointmentSource::web->value,
    ]);

    $response = $this->actingAs($barberUser)
        ->patchJson("/api/v1/barber/appointments/{$appointment->id}/complete", [
            'verification_code' => 'CC-ESNNSR',
        ]);

    $response->assertOk();
    expect($appointment->fresh()->status)->toBe(AppointmentStatus::completed);
});

it('accepts lowercase, whitespace, leading hash, or 6-char suffix when verifying CC-XXXXXX codes', function () {
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $customer = User::factory()->customer()->create();
    $service = Service::factory()->create();

    $appt1 = Appointment::factory()->create([
        'booking_reference' => 'CC-ESNNSR',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::confirmed->value,
        'source' => AppointmentSource::web->value,
    ]);

    $this->actingAs($barberUser)
        ->patchJson("/api/v1/barber/appointments/{$appt1->id}/complete", [
            'verification_code' => ' #cc-esnnsr ',
        ])
        ->assertOk();

    expect($appt1->fresh()->status)->toBe(AppointmentStatus::completed);

    $appt2 = Appointment::factory()->create([
        'booking_reference' => 'CC-ABC789',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::confirmed->value,
        'source' => AppointmentSource::app->value,
    ]);

    $this->actingAs($barberUser)
        ->patchJson("/api/v1/barber/appointments/{$appt2->id}/complete", [
            'verification_code' => 'abc789',
        ])
        ->assertOk();

    expect($appt2->fresh()->status)->toBe(AppointmentStatus::completed);
});

it('rejects missing or mismatched verification code for online appointments', function () {
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $customer = User::factory()->customer()->create();
    $service = Service::factory()->create();

    $appointment = Appointment::factory()->create([
        'booking_reference' => 'CC-ESNNSR',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::confirmed->value,
        'source' => AppointmentSource::web->value,
    ]);

    $missingResp = $this->actingAs($barberUser)
        ->patchJson("/api/v1/barber/appointments/{$appointment->id}/complete", []);

    $missingResp->assertStatus(422);
    expect($missingResp->json('error.code'))->toBe('VALIDATION_FAILED');
    expect($missingResp->json('error.details.field_errors.verification_code'))->not->toBeNull();

    $wrongResp = $this->actingAs($barberUser)
        ->patchJson("/api/v1/barber/appointments/{$appointment->id}/complete", [
            'verification_code' => 'CC-WRONG1',
        ]);

    $wrongResp->assertStatus(422);
    expect($wrongResp->json('error.code'))->toBe('VALIDATION_FAILED');
    expect($appointment->fresh()->status)->toBe(AppointmentStatus::confirmed);
});

it('allows walk-in appointments to complete without a verification code', function () {
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $service = Service::factory()->create();

    $walkIn = Appointment::factory()->walkIn()->create([
        'booking_reference' => 'CC-WLK123',
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::confirmed->value,
        'source' => AppointmentSource::walk_in->value,
    ]);

    $this->actingAs($barberUser)
        ->patchJson("/api/v1/barber/appointments/{$walkIn->id}/complete", [])
        ->assertOk();

    expect($walkIn->fresh()->status)->toBe(AppointmentStatus::completed);
});

it('rejects completing a pending appointment even with a matching code', function () {
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $customer = User::factory()->customer()->create();
    $service = Service::factory()->create();

    $appointment = Appointment::factory()->create([
        'booking_reference' => 'CC-PEND01',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::pending->value,
        'source' => AppointmentSource::web->value,
    ]);

    $response = $this->actingAs($barberUser)
        ->patchJson("/api/v1/barber/appointments/{$appointment->id}/complete", [
            'verification_code' => 'CC-PEND01',
        ]);

    $response->assertStatus(409);
    expect($response->json('error.code'))->toBe('BOOKING_INVALID_TRANSITION');
    expect($appointment->fresh()->status)->toBe(AppointmentStatus::pending);
});

it('forbids another barber or customer from completing an appointment', function () {
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $otherBarberUser = User::factory()->barber()->create();
    Barber::factory()->create(['user_id' => $otherBarberUser->id]);
    $customer = User::factory()->customer()->create();
    $service = Service::factory()->create();

    $appointment = Appointment::factory()->create([
        'booking_reference' => 'CC-AUTH01',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::confirmed->value,
        'source' => AppointmentSource::web->value,
    ]);

    $this->actingAs($otherBarberUser)
        ->patchJson("/api/v1/barber/appointments/{$appointment->id}/complete", [
            'verification_code' => 'CC-AUTH01',
        ])
        ->assertForbidden();

    $this->actingAs($customer)
        ->patchJson("/api/v1/barber/appointments/{$appointment->id}/complete", [
            'verification_code' => 'CC-AUTH01',
        ])
        ->assertForbidden();
});
