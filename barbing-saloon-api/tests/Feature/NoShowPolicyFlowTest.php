<?php

declare(strict_types=1);

use App\Domain\Booking\Services\AvailabilityEngine;
use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Mail\NoShowWarning;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Branch;
use App\Models\DeviceToken;
use App\Models\NoShowRecord;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Mail::fake();
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('allows admin to configure no-show timing, grace limit, and consequence threshold and denies non-admins', function (): void {
    $admin = User::factory()->admin()->create();
    $barberUser = User::factory()->barber()->create();
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->postJson('/api/v1/admin/no-shows/policy', ['no_show_delay_minutes' => 20])
        ->assertForbidden();

    $this->actingAs($barberUser)
        ->getJson('/api/v1/admin/no-shows/policy')
        ->assertForbidden();

    $res = $this->actingAs($admin)
        ->postJson('/api/v1/admin/no-shows/policy', [
            'no_show_delay_minutes' => 20,
            'no_show_warning_lead_minutes' => 5,
            'no_show_grace_limit' => 1,
            'no_show_grace_extension_minutes' => 15,
            'no_show_threshold' => 2,
            'no_show_threshold_window_days' => 60,
            'no_show_auto_mark_enabled' => true,
            'no_show_auto_forfeit_deposit' => false,
        ])
        ->assertOk();

    expect($res->json('data.no_show_delay_minutes'))->toBe(20);
    expect($res->json('data.no_show_grace_limit'))->toBe(1);
    expect($res->json('data.no_show_threshold'))->toBe(2);
    expect(Setting::where('key', 'no_show_delay_minutes')->value('value'))->toBe('20');
});

it('sends email + web in-app + app push warning before deadline, supports one-time grace extension with reason, then auto-marks and frees slot', function (): void {
    Carbon::setTestNow(Carbon::create(2026, 11, 26, 10, 11, 0, 'Africa/Lagos'));

    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create(['email' => 'late.client@example.com']);
    $otherCustomer = User::factory()->customer()->create();
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $branch = Branch::factory()->create();
    $service = Service::factory()->create(['duration_minutes' => 30]);

    WorkingHour::create([
        'barber_id' => $barber->id,
        'day_of_week' => Carbon::create(2026, 11, 26, 10, 0, 0, 'Africa/Lagos')->dayOfWeek,
        'open_time' => '08:00:00',
        'close_time' => '20:00:00',
        'is_closed' => false,
    ]);

    DeviceToken::create([
        'user_id' => $customer->id,
        'token' => 'ExponentPushToken[test-client-device]',
        'platform' => 'android',
        'is_revoked' => false,
    ]);

    $this->actingAs($admin)->postJson('/api/v1/admin/no-shows/policy', [
        'no_show_delay_minutes' => 15,
        'no_show_warning_lead_minutes' => 5,
        'no_show_grace_limit' => 1,
        'no_show_grace_extension_minutes' => 15,
        'no_show_threshold' => 2,
        'no_show_threshold_window_days' => 90,
        'no_show_auto_mark_enabled' => true,
        'no_show_auto_forfeit_deposit' => false,
    ])->assertOk();

    $appointment = Appointment::factory()->create([
        'booking_reference' => 'CC-LATE01',
        'branch_id' => $branch->id,
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'appointment_date' => '2026-11-26',
        'appointment_time' => '10:00:00',
        'end_time' => '10:30:00',
        'total_duration_minutes' => 30,
        'status' => AppointmentStatus::confirmed->value,
        'deposit_paid' => true,
        'payment_status' => AppointmentPaymentStatus::paid->value,
    ]);

    // 10:11 is inside [10:10, 10:15) warning window -> warning dispatched once
    $this->artisan('bookings:process-no-shows')->assertExitCode(0);

    Mail::assertQueued(NoShowWarning::class, 1);
    $warningNotif = Notification::where('recipient_id', $customer->id)
        ->where('type', 'booking_no_show_warning')
        ->first();
    expect($warningNotif)->not->toBeNull();
    expect(NotificationDelivery::where('notification_id', $warningNotif->id)->where('channel', 'in_app')->exists())->toBeTrue();
    expect(NotificationDelivery::where('notification_id', $warningNotif->id)->where('channel', 'push')->exists())->toBeTrue();
    expect($appointment->fresh()->no_show_warning_sent_at)->not->toBeNull();

    // Another customer cannot extend (IDOR protection)
    $this->actingAs($otherCustomer)
        ->postJson("/api/v1/appointments/{$appointment->id}/grace-extension", ['reason' => 'Not my booking'])
        ->assertForbidden();

    // Missing/short reason fails validation
    $this->actingAs($customer)
        ->postJson("/api/v1/appointments/{$appointment->id}/grace-extension", ['reason' => 'hi'])
        ->assertStatus(422);

    // Valid one-time grace extension pushes deadline from 10:15 to 10:30
    $this->actingAs($customer)
        ->postJson("/api/v1/appointments/{$appointment->id}/grace-extension", [
            'reason' => 'Stuck in traffic on Keffi-Abuja highway',
        ])
        ->assertOk()
        ->assertJsonPath('data.grace_extension_count', 1);

    // Second extension attempt is rejected (grace_limit = 1)
    $this->actingAs($customer)
        ->postJson("/api/v1/appointments/{$appointment->id}/grace-extension", [
            'reason' => 'Still stuck in traffic',
        ])
        ->assertStatus(422);

    // At 10:18 (past original 10:15, before extended 10:30), appointment stays confirmed
    Carbon::setTestNow(Carbon::create(2026, 11, 26, 10, 18, 0, 'Africa/Lagos'));
    $this->artisan('bookings:process-no-shows')->assertExitCode(0);
    expect($appointment->fresh()->status)->toBe(AppointmentStatus::confirmed);

    // At 10:31 (past extended 10:30), appointment is auto-marked no_show and slot opens up
    Carbon::setTestNow(Carbon::create(2026, 11, 26, 10, 31, 0, 'Africa/Lagos'));
    $this->artisan('bookings:process-no-shows')->assertExitCode(0);

    $appointment->refresh();
    expect($appointment->status)->toBe(AppointmentStatus::no_show);
    expect($appointment->no_show_auto)->toBeTrue();
    expect($appointment->deposit_forfeited)->toBeFalse(); // Manual forfeit button mode
    expect($customer->fresh()->no_show_count)->toBe(1);
    expect(NoShowRecord::where('appointment_id', $appointment->id)->exists())->toBeTrue();

    // Slot for a later booking at 10:00 (on future or free window) is no longer blocked by no_show
    $slots = app(AvailabilityEngine::class)->calculateAvailableSlots(
        Carbon::parse('2026-11-26', 'Africa/Lagos'),
        $barber->id,
        30,
        false,
        $branch->id
    );
    expect($slots)->toContain('11:30');
});

it('allows barber to mark no-show and explicitly forfeit deposit, enforces threshold consequences, and lets admin resolve appeals & initiate refund', function (): void {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create(['email' => 'repeat.noshow@example.com']);
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $otherBarberUser = User::factory()->barber()->create();
    Barber::factory()->create(['user_id' => $otherBarberUser->id]);
    $service = Service::factory()->create(['price' => 5000]);

    Setting::updateOrCreate(['key' => 'no_show_threshold'], ['value' => '1', 'group' => 'booking']);

    $appointment = Appointment::factory()->create([
        'booking_reference' => 'CC-FORF01',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'grand_total' => 5000,
        'total_amount' => 5000,
        'total_price' => 5000,
        'status' => AppointmentStatus::confirmed->value,
        'deposit_paid' => true,
        'payment_status' => AppointmentPaymentStatus::paid->value,
    ]);

    $payment = Payment::create([
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'amount' => 5000,
        'amount_minor' => 500000,
        'currency' => 'NGN',
        'status' => PaymentStatus::successful->value,
        'provider' => 'paystack',
        'payment_method' => 'card',
        'reference' => 'PSK-NOSHOW-01',
    ]);

    // Unassigned barber cannot mark no-show
    $this->actingAs($otherBarberUser)
        ->patchJson("/api/v1/barber/appointments/{$appointment->id}/no-show")
        ->assertForbidden();

    // Assigned barber marks no-show -> threshold=1 triggers requires_deposit
    $this->actingAs($barberUser)
        ->patchJson("/api/v1/barber/appointments/{$appointment->id}/no-show", [
            'reason' => 'Client did not arrive',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'no_show');

    expect($customer->fresh()->no_show_count)->toBe(1);
    expect($customer->fresh()->requires_deposit)->toBeTrue();

    // Explicit "Forfeit Deposit" button endpoint
    $this->actingAs($barberUser)
        ->postJson("/api/v1/barber/appointments/{$appointment->id}/forfeit-deposit", [
            'reason' => 'Deposit forfeited per policy',
        ])
        ->assertOk()
        ->assertJsonPath('data.deposit_forfeited', true)
        ->assertJsonPath('data.deposit_forfeited_minor', 500000);

    $record = NoShowRecord::where('appointment_id', $appointment->id)->firstOrFail();

    // Customer submits appeal
    $this->actingAs($customer)
        ->postJson("/api/v1/no-shows/{$record->id}/appeal", [
            'appeal_reason' => 'I fell suddenly ill and was admitted to the clinic.',
        ])
        ->assertOk()
        ->assertJsonPath('data.appeal_outcome', 'pending');

    // Admin reviews dashboard, approves appeal, and initiates refund
    $this->actingAs($admin)
        ->getJson('/api/v1/admin/no-shows?filter=pending_appeals')
        ->assertOk()
        ->assertJsonPath('data.stats.pending_appeals', 1);

    $this->actingAs($admin)
        ->patchJson("/api/v1/admin/no-shows/{$record->id}/appeal", [
            'outcome' => 'approved',
            'resolution_note' => 'Medical emergency verified.',
            'initiate_refund' => true,
        ])
        ->assertOk()
        ->assertJsonPath('data.appeal_outcome', 'approved')
        ->assertJsonPath('data.deposit_forfeited', false)
        ->assertJsonPath('data.appointment.payment_status', 'refunded');

    expect($customer->fresh()->no_show_count)->toBe(0);
    expect($customer->fresh()->requires_deposit)->toBeFalse();
    expect($payment->fresh()->status)->toBe(PaymentStatus::refunded);
});
