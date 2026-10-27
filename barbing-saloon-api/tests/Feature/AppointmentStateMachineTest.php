<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Booking\AppointmentStateMachine;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Events\AppointmentStatusChanged;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('allows legal status transitions in AppointmentStateMachine', function () {
    expect(AppointmentStateMachine::isAllowed('pending', 'confirmed'))->toBeTrue();
    expect(AppointmentStateMachine::isAllowed('pending', 'cancelled'))->toBeTrue();
    expect(AppointmentStateMachine::isAllowed('confirmed', 'checked_in'))->toBeTrue();
    expect(AppointmentStateMachine::isAllowed('confirmed', 'no_show'))->toBeTrue();
    expect(AppointmentStateMachine::isAllowed('confirmed', 'cancelled'))->toBeTrue();
    expect(AppointmentStateMachine::isAllowed('checked_in', 'in_progress'))->toBeTrue();
    expect(AppointmentStateMachine::isAllowed('checked_in', 'cancelled'))->toBeTrue();
    expect(AppointmentStateMachine::isAllowed('in_progress', 'completed'))->toBeTrue();

    // Terminal statuses
    expect(AppointmentStateMachine::isAllowed('completed', 'confirmed'))->toBeFalse();
    expect(AppointmentStateMachine::isAllowed('cancelled', 'confirmed'))->toBeFalse();
    expect(AppointmentStateMachine::isAllowed('no_show', 'confirmed'))->toBeFalse();

    // Illegal jumps
    expect(AppointmentStateMachine::isAllowed('pending', 'in_progress'))->toBeFalse();
    expect(AppointmentStateMachine::isAllowed('pending', 'completed'))->toBeFalse();
});

it('enforces role authorization on state transitions', function () {
    // Customers can cancel pending bookings
    expect(AppointmentStateMachine::isRoleAllowed('pending', 'cancelled', 'customer'))->toBeTrue();
    // Customers cannot confirm bookings
    expect(AppointmentStateMachine::isRoleAllowed('pending', 'confirmed', 'customer'))->toBeFalse();
    // Customers cannot start haircuts or check in
    expect(AppointmentStateMachine::isRoleAllowed('confirmed', 'checked_in', 'customer'))->toBeFalse();
    expect(AppointmentStateMachine::isRoleAllowed('checked_in', 'in_progress', 'customer'))->toBeFalse();

    // Barbers and admins can confirm and progress
    expect(AppointmentStateMachine::isRoleAllowed('pending', 'confirmed', 'barber'))->toBeTrue();
    expect(AppointmentStateMachine::isRoleAllowed('confirmed', 'checked_in', 'barber'))->toBeTrue();
    expect(AppointmentStateMachine::isRoleAllowed('checked_in', 'in_progress', 'barber'))->toBeTrue();
    expect(AppointmentStateMachine::isRoleAllowed('in_progress', 'completed', 'barber'))->toBeTrue();
    expect(AppointmentStateMachine::isRoleAllowed('in_progress', 'completed', 'admin'))->toBeTrue();
});

it('persists checked_in and in_progress statuses (DATA-1 fix) and logs history', function () {
    Event::fake([AppointmentStatusChanged::class]);

    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $customer = User::factory()->customer()->create();
    $service = Service::factory()->create();

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::confirmed->value,
    ]);

    // Transition confirmed -> checked_in
    $response1 = $this->actingAs($barberUser)
        ->patchJson("/api/v1/appointments/{$appointment->id}/status", [
            'status' => 'checked_in',
            'reason' => 'Client arrived at the salon',
        ]);

    $response1->assertOk();
    $appointment->refresh();
    expect($appointment->status)->toBe(AppointmentStatus::checked_in);

    // Verify history recorded
    expect(AppointmentStatusHistory::where('appointment_id', $appointment->id)
        ->where('new_status', 'checked_in')
        ->exists())->toBeTrue();

    // Transition checked_in -> in_progress
    $response2 = $this->actingAs($barberUser)
        ->patchJson("/api/v1/appointments/{$appointment->id}/status", [
            'status' => 'in_progress',
            'reason' => 'Starting haircut service',
        ]);

    $response2->assertOk();
    $appointment->refresh();
    expect($appointment->status)->toBe(AppointmentStatus::in_progress);

    // Verify domain event dispatched
    Event::assertDispatched(AppointmentStatusChanged::class, function ($event) use ($appointment) {
        return $event->appointment->id === $appointment->id && $event->newStatus === 'in_progress';
    });
});

it('rejects illegal status transition with BOOKING_INVALID_TRANSITION', function () {
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $customer = User::factory()->customer()->create();
    $service = Service::factory()->create();

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::pending->value,
    ]);

    // Illegal jump from pending directly to completed
    $response = $this->actingAs($barberUser)
        ->patchJson("/api/v1/appointments/{$appointment->id}/status", [
            'status' => 'completed',
        ]);

    $response->assertStatus(409);
    expect($response->json('error.code'))->toBe('BOOKING_INVALID_TRANSITION');
});

it('requires a valid barber_id for walk-in if actor is not a barber (DATA-2 fix)', function () {
    $admin = User::factory()->admin()->create();
    $service = Service::factory()->create();

    // Attempt walk-in booking without barber_id as admin
    $response = $this->actingAs($admin)
        ->postJson('/api/v1/appointments/walk-in', [
            'service_id' => $service->id,
            'client_name' => 'Walk In Guest',
        ]);

    $response->assertStatus(422);
    expect($response->json('error.code'))->toBe('VALIDATION_FAILED');
    expect($response->json('error.details.field_errors.barber_id'))->not->toBeNull();
});

it('denies customers access to walk-in booking', function () {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->postJson('/api/v1/appointments/walk-in', [])
        ->assertForbidden();
});

it('creates an unpaid walk-in only for an authorized barber', function () {
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $service = Service::factory()->create();

    $response = $this->actingAs($barberUser)
        ->postJson('/api/v1/appointments/walk-in', [
            'service_id' => $service->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'client_name' => 'Walk In Guest',
        ]);

    $response->assertCreated();
    expect(Appointment::findOrFail($response->json('data.id'))->barber_id)->toBe($barber->id);
    expect(Appointment::findOrFail($response->json('data.id'))->deposit_paid)->toBeFalse();
});

it('prevents exact double booking at the database level via active_slot_key (C3 backstop)', function () {
    $barber = Barber::factory()->create();
    $customer1 = User::factory()->customer()->create();
    $customer2 = User::factory()->customer()->create();
    $service = Service::factory()->create();

    $date = '2026-10-15';
    $time = '10:00';

    // Insert first appointment
    Appointment::create([
        'booking_reference' => 'CC-TEST1',
        'customer_id' => $customer1->id,
        'client_name' => 'Customer One',
        'client_phone' => '+2348011112222',
        'client_email' => 'one@example.com',
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'appointment_date' => $date,
        'appointment_time' => $time,
        'total_duration_minutes' => 30,
        'total_price' => 5000,
        'status' => AppointmentStatus::confirmed->value,
    ]);

    // Attempt to insert duplicate exact appointment for same barber, date, and time
    expect(function () use ($barber, $customer2, $service, $date, $time) {
        Appointment::create([
            'booking_reference' => 'CC-TEST2',
            'customer_id' => $customer2->id,
            'client_name' => 'Customer Two',
            'client_phone' => '+2348033334444',
            'client_email' => 'two@example.com',
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'total_duration_minutes' => 30,
            'total_price' => 5000,
            'status' => AppointmentStatus::confirmed->value,
        ]);
    })->toThrow(QueryException::class);
});
