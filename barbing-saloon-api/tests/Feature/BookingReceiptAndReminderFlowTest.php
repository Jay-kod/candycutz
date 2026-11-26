<?php

declare(strict_types=1);

use App\Domain\Notification\Services\TransactionalMailRunner;
use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Services\PaymentConfirmer;
use App\Domain\Shared\Enums\AppointmentSource;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\Shared\Enums\UserRole;
use App\Mail\BookingConfirmation;
use App\Mail\BookingReminder;
use App\Mail\BookingThankYou;
use App\Mail\NotificationMail;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Branch;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Mail::fake();
    Config::set('app.frontend_url', 'https://app.candycutz.test');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('sends the redesigned official receipt email with booking code once upon paystack payment confirmation without generic duplicate email', function (): void {
    $customer = User::factory()->customer()->create(['email' => 'vip.client@example.com', 'name' => 'Adebayo Cole']);
    $barberUser = User::factory()->barber()->create(['name' => 'Master Tunde']);
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $branch = Branch::factory()->create();
    $service = Service::factory()->create(['name' => 'Executive Skin Fade', 'price' => 5000]);

    $appointment = Appointment::factory()->create([
        'booking_reference' => 'CC-PAY001',
        'branch_id' => $branch->id,
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'appointment_date' => '2026-11-28',
        'appointment_time' => '15:00:00',
        'grand_total' => 5000,
        'total_amount' => 5000,
        'total_price' => 5000,
        'status' => AppointmentStatus::pending,
        'payment_status' => AppointmentPaymentStatus::unpaid,
        'deposit_paid' => false,
    ]);

    $payment = Payment::create([
        'appointment_id' => $appointment->id,
        'customer_id' => $customer->id,
        'amount' => 5000,
        'amount_minor' => 500000,
        'currency' => 'NGN',
        'status' => PaymentStatus::pending->value,
        'provider' => 'paystack',
        'payment_method' => 'card',
        'reference' => 'PSK-REF-001',
    ]);

    app(PaymentConfirmer::class)->confirm('PSK-REF-001', 'webhook', ['channel' => 'card']);

    // Replaying idempotently must not dispatch a second receipt email
    app(PaymentConfirmer::class)->confirm('PSK-REF-001', 'webhook', ['channel' => 'card']);

    Mail::assertQueued(BookingConfirmation::class, 1);
    Mail::assertQueued(BookingConfirmation::class, function (BookingConfirmation $mail) use ($payment): bool {
        $html = $mail->render();
        $expectedReceipt = 'RCP-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);

        return $mail->hasTo('vip.client@example.com')
            && $mail->hasSubject('Your CandyCutz Official Receipt & Booking Code (CC-PAY001)')
            && str_contains($html, 'CC-PAY001')
            && str_contains($html, $expectedReceipt)
            && str_contains($html, 'PSK-REF-001')
            && str_contains($html, '5,000.00')
            && str_contains($html, 'Booking &amp; Service Verification Code');
    });

    Mail::assertNotQueued(NotificationMail::class);

    expect(Notification::where('recipient_id', $customer->id)->where('type', 'payment_verified')->exists())->toBeTrue();
    expect(Notification::where('recipient_type', 'all_admins')->where('type', 'payment_verified')->exists())->toBeTrue();
    $trackedEmailDeliveries = NotificationDelivery::where('channel', 'email')
        ->whereHas('notification', fn ($q) => $q->where('type', 'payment_verified'))
        ->get();
    expect($trackedEmailDeliveries)->toHaveCount(1);
    expect($trackedEmailDeliveries->first()->status)->toBe('delivered');
});

it('handles transfer auto_confirm enabled vs manual verification before sending the official booking receipt email', function (): void {
    $customer = User::factory()->customer()->create(['email' => 'transfer.user@example.com']);
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $service = Service::factory()->create(['price' => 12000]);

    Setting::updateOrCreate(['key' => 'payment_auto_confirm'], ['value' => 'true', 'type' => 'boolean', 'group' => 'payment']);

    $autoAppt = Appointment::factory()->create([
        'booking_reference' => 'CC-XFER01',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'grand_total' => 12000,
        'total_amount' => 12000,
        'status' => AppointmentStatus::pending->value,
        'payment_status' => AppointmentPaymentStatus::unpaid->value,
        'deposit_paid' => false,
    ]);

    $this->actingAs($customer)
        ->postJson('/api/v1/payments/confirm-transfer', [
            'appointment_id' => $autoAppt->id,
            'barber_id' => $barber->id,
            'payment_method' => 'bank_transfer',
            'amount' => 12000,
            'reference' => 'XFER-AUTO-001',
        ])
        ->assertOk()
        ->assertJsonPath('data.appointment.verification_code', 'CC-XFER01')
        ->assertJsonPath('data.payment_status', 'paid');

    Mail::assertQueued(BookingConfirmation::class, function (BookingConfirmation $mail): bool {
        return $mail->hasTo('transfer.user@example.com')
            && str_contains($mail->render(), 'CC-XFER01');
    });

    // When auto_confirm is false, hold official receipt until admin verification
    Mail::fake();
    Setting::updateOrCreate(['key' => 'payment_auto_confirm'], ['value' => 'false', 'type' => 'boolean', 'group' => 'payment']);

    $manualAppt = Appointment::factory()->create([
        'booking_reference' => 'CC-MAN002',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'grand_total' => 12000,
        'total_amount' => 12000,
        'status' => AppointmentStatus::pending->value,
        'payment_status' => AppointmentPaymentStatus::unpaid->value,
        'deposit_paid' => false,
    ]);

    $this->actingAs($customer)
        ->postJson('/api/v1/payments/confirm-transfer', [
            'appointment_id' => $manualAppt->id,
            'barber_id' => $barber->id,
            'payment_method' => 'bank_transfer',
            'amount' => 12000,
            'reference' => 'XFER-MANUAL-002',
        ])
        ->assertOk()
        ->assertJsonPath('data.payment_status', 'awaiting_verification');

    Mail::assertNotQueued(BookingConfirmation::class);
    expect(Notification::where('recipient_id', $customer->id)->where('related_entity_id', $manualAppt->id)->where('type', 'payment_received')->exists())->toBeTrue();
});

it('sends the official receipt email when admin verifies a payment or barber collects venue payment', function (): void {
    $admin = User::factory()->create(['role' => UserRole::admin]);
    $customer = User::factory()->customer()->create(['email' => 'venue.client@example.com']);
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $service = Service::factory()->create(['price' => 8000]);

    $awaitingAppt = Appointment::factory()->create([
        'booking_reference' => 'CC-ADMV01',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'grand_total' => 8000,
        'status' => AppointmentStatus::pending->value,
        'payment_status' => AppointmentPaymentStatus::unpaid->value,
        'deposit_paid' => false,
    ]);

    $payment = Payment::create([
        'appointment_id' => $awaitingAppt->id,
        'customer_id' => $customer->id,
        'amount' => 8000,
        'amount_minor' => 800000,
        'currency' => 'NGN',
        'status' => PaymentStatus::awaiting_verification->value,
        'provider' => 'manual_transfer',
        'payment_method' => 'bank_transfer',
        'reference' => 'MAN-ADM-001',
    ]);

    $this->actingAs($admin)
        ->postJson("/api/v1/admin/payments/{$payment->id}/verify")
        ->assertOk();

    Mail::assertQueued(BookingConfirmation::class, function (BookingConfirmation $mail): bool {
        return $mail->hasTo('venue.client@example.com')
            && str_contains($mail->render(), 'CC-ADMV01');
    });

    // Barber venue payment collection flow
    Mail::fake();
    $venueAppt = Appointment::factory()->create([
        'booking_reference' => 'CC-VENU02',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'grand_total' => 8000,
        'status' => AppointmentStatus::confirmed->value,
        'payment_status' => AppointmentPaymentStatus::unpaid->value,
        'deposit_paid' => false,
    ]);

    $this->actingAs($barberUser)
        ->postJson("/api/v1/barber/appointments/{$venueAppt->id}/collect-payment")
        ->assertOk();

    Mail::assertQueued(BookingConfirmation::class, function (BookingConfirmation $mail): bool {
        return $mail->hasTo('venue.client@example.com')
            && str_contains($mail->render(), 'CC-VENU02');
    });
});

it('sends the session completion thank-you email with booking code when completed by barber or verified by admin', function (): void {
    $admin = User::factory()->create(['role' => UserRole::admin]);
    $customer = User::factory()->customer()->create(['email' => 'finished.cut@example.com']);
    $barberUser = User::factory()->barber()->create(['name' => 'Barber Musa']);
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $service = Service::factory()->create(['name' => 'Luxury Beard Sculpt']);

    $barberAppt = Appointment::factory()->create([
        'booking_reference' => 'CC-DONE01',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::confirmed->value,
        'source' => AppointmentSource::web->value,
        'deposit_paid' => true,
        'payment_status' => AppointmentPaymentStatus::paid->value,
    ]);

    $this->actingAs($barberUser)
        ->patchJson("/api/v1/barber/appointments/{$barberAppt->id}/complete", [
            'verification_code' => 'CC-DONE01',
        ])
        ->assertOk();

    Mail::assertQueued(BookingThankYou::class, function (BookingThankYou $mail): bool {
        $html = $mail->render();

        return $mail->hasTo('finished.cut@example.com')
            && $mail->hasSubject('Thank You for Visiting CandyCutz')
            && str_contains($html, 'CC-DONE01')
            && str_contains($html, 'Session Verified &amp; Completed')
            && str_contains($html, '/customer/dashboard/reviews');
    });

    Mail::assertNotQueued(NotificationMail::class);

    $custNotif = Notification::where('recipient_id', $customer->id)
        ->where('related_entity_id', $barberAppt->id)
        ->where('type', 'booking_completed')
        ->first();

    expect($custNotif)->not->toBeNull();
    expect($custNotif->action_url)->toContain('/customer/dashboard/reviews');

    // Admin verification endpoint should also dispatch completion email cleanly
    Mail::fake();
    $adminVerifyAppt = Appointment::factory()->create([
        'booking_reference' => 'CC-ADMDN2',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::confirmed->value,
        'source' => AppointmentSource::web->value,
    ]);

    $this->actingAs($admin)
        ->patchJson("/api/v1/admin/verifications/{$adminVerifyAppt->id}/verify")
        ->assertOk();

    Mail::assertQueued(BookingThankYou::class, function (BookingThankYou $mail): bool {
        return $mail->hasTo('finished.cut@example.com')
            && str_contains($mail->render(), 'CC-ADMDN2');
    });
});

it('dispatches 30-minute pre-booking reminders to customer email and in-app, barber in-app, and admin in-app only with idempotency', function (): void {
    Carbon::setTestNow(Carbon::create(2026, 11, 25, 14, 0, 0, 'Africa/Lagos'));

    $admin = User::factory()->create(['role' => UserRole::admin, 'email' => 'admin.noemail@example.com']);
    $customer = User::factory()->customer()->create(['email' => 'timely.guest@example.com', 'name' => 'Ibrahim Sani']);
    $barberUser = User::factory()->barber()->create(['email' => 'barber.lead@example.com', 'name' => 'Chef Kazeem']);
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $service = Service::factory()->create(['name' => 'Precision Taper']);

    // Due in 25 minutes -> MUST trigger
    $dueAppt = Appointment::factory()->create([
        'booking_reference' => 'CC-REM25M',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'appointment_date' => '2026-11-25',
        'appointment_time' => '14:25:00',
        'status' => AppointmentStatus::confirmed->value,
        'deposit_paid' => true,
        'payment_status' => AppointmentPaymentStatus::paid->value,
    ]);

    // Due in 50 minutes -> outside 30-min window
    $tooEarlyAppt = Appointment::factory()->create([
        'booking_reference' => 'CC-EARLY50',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'appointment_date' => '2026-11-25',
        'appointment_time' => '14:50:00',
        'status' => AppointmentStatus::confirmed->value,
        'deposit_paid' => true,
    ]);

    // Already started 10 minutes ago -> must NOT trigger
    $pastAppt = Appointment::factory()->create([
        'booking_reference' => 'CC-PAST10',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'appointment_date' => '2026-11-25',
        'appointment_time' => '13:50:00',
        'status' => AppointmentStatus::confirmed->value,
        'deposit_paid' => true,
    ]);

    // Cancelled even within 20 minutes -> must NOT trigger
    $cancelledAppt = Appointment::factory()->create([
        'booking_reference' => 'CC-CANC20',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'appointment_date' => '2026-11-25',
        'appointment_time' => '14:20:00',
        'status' => AppointmentStatus::cancelled->value,
    ]);

    // Unpaid pending within 20 minutes -> must NOT trigger
    $unpaidPendingAppt = Appointment::factory()->create([
        'booking_reference' => 'CC-UNPD20',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'appointment_date' => '2026-11-25',
        'appointment_time' => '14:20:00',
        'status' => AppointmentStatus::pending->value,
        'deposit_paid' => false,
    ]);

    $this->artisan('bookings:send-reminders', ['--minutes' => 30])->assertExitCode(0);

    // Only the customer of $dueAppt receives the luxury BookingReminder email (never admins)
    Mail::assertQueued(BookingReminder::class, 1);
    Mail::assertQueued(BookingReminder::class, function (BookingReminder $mail) use ($admin, $customer): bool {
        $html = $mail->render();

        return $mail->hasTo($customer->email)
            && ! $mail->hasTo($admin->email)
            && $mail->hasSubject('Reminder: Your CandyCutz Appointment Starts in 30 Minutes (CC-REM25M)')
            && str_contains($html, 'CC-REM25M')
            && str_contains($html, '2:25 PM');
    });

    Mail::assertNotQueued(NotificationMail::class);

    // In-app notifications for customer, barber, and all_admins (no admin email delivery)
    $custNotif = Notification::where('recipient_id', $customer->id)
        ->where('related_entity_id', $dueAppt->id)
        ->where('type', 'booking_reminder')
        ->first();
    $barberNotif = Notification::where('recipient_id', $barberUser->id)
        ->where('related_entity_id', $dueAppt->id)
        ->where('type', 'booking_reminder')
        ->first();
    $adminBroadcast = Notification::where('recipient_type', 'all_admins')
        ->where('related_entity_id', $dueAppt->id)
        ->where('type', 'booking_reminder')
        ->first();

    expect($custNotif)->not->toBeNull();
    expect($custNotif->action_url)->toBe('/customer/dashboard/bookings');
    expect($barberNotif)->not->toBeNull();
    expect($barberNotif->action_url)->toBe('/barber/appointments');
    expect($adminBroadcast)->not->toBeNull();
    expect($adminBroadcast->action_url)->toBe('/admin/appointments');
    expect(NotificationDelivery::where('notification_id', $adminBroadcast->id)->where('channel', 'in_app')->exists())->toBeTrue();
    expect(NotificationDelivery::where('notification_id', $adminBroadcast->id)->where('channel', 'email')->exists())->toBeFalse();

    // reminder_30m_sent_at stamped only on eligible appointment
    expect($dueAppt->fresh()->reminder_30m_sent_at)->not->toBeNull();
    expect($tooEarlyAppt->fresh()->reminder_30m_sent_at)->toBeNull();
    expect($pastAppt->fresh()->reminder_30m_sent_at)->toBeNull();
    expect($cancelledAppt->fresh()->reminder_30m_sent_at)->toBeNull();
    expect($unpaidPendingAppt->fresh()->reminder_30m_sent_at)->toBeNull();

    // Running again a minute later must remain strictly idempotent
    Carbon::setTestNow(Carbon::create(2026, 11, 25, 14, 1, 0, 'Africa/Lagos'));
    $this->artisan('bookings:send-reminders', ['--minutes' => 30])->assertExitCode(0);
    Mail::assertQueued(BookingReminder::class, 1);
});

it('prevents reminder starvation when large un-reminded backlog exists on prior day', function (): void {
    Carbon::setTestNow(Carbon::create(2026, 11, 25, 14, 0, 0, 'Africa/Lagos'));

    $customer = User::factory()->customer()->create(['email' => 'backlog.guest@example.com']);
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);
    $branch = Branch::factory()->create();
    $service = Service::factory()->create();

    // Seed 260 historical un-reminded rows from yesterday (> 250 batch boundary)
    $yesterdayRows = [];
    for ($i = 0; $i < 260; $i++) {
        $hour = intdiv($i, 60) % 24;
        $minute = $i % 60;
        $startAt = sprintf('%02d:%02d:00', $hour, $minute);
        $endAt = sprintf('%02d:%02d:59', $hour, $minute);
        $yesterdayRows[] = [
            'booking_reference' => 'CC-OLD'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_type' => 'in_shop',
            'client_name' => 'Historical Client',
            'client_phone' => '08000000000',
            'client_email' => 'historical@example.com',
            'appointment_date' => '2026-11-24',
            'appointment_time' => $startAt,
            'end_time' => $endAt,
            'total_duration_minutes' => 30,
            'total_amount' => 5000,
            'grand_total' => 5000,
            'total_price' => 5000,
            'status' => AppointmentStatus::confirmed->value,
            'deposit_paid' => true,
            'payment_status' => AppointmentPaymentStatus::paid->value,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    foreach (array_chunk($yesterdayRows, 40) as $batch) {
        Appointment::insert($batch);
    }

    $dueOne = Appointment::factory()->create([
        'booking_reference' => 'CC-DUE001',
        'branch_id' => $branch->id,
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'appointment_date' => '2026-11-25',
        'appointment_time' => '14:20:00',
        'status' => AppointmentStatus::confirmed->value,
        'deposit_paid' => true,
        'payment_status' => AppointmentPaymentStatus::paid->value,
    ]);

    $dueTwo = Appointment::factory()->create([
        'booking_reference' => 'CC-DUE002',
        'branch_id' => $branch->id,
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'appointment_date' => '2026-11-25',
        'appointment_time' => '14:29:00',
        'status' => AppointmentStatus::confirmed->value,
        'deposit_paid' => true,
        'payment_status' => AppointmentPaymentStatus::paid->value,
    ]);

    $this->artisan('bookings:send-reminders', ['--minutes' => 30])->assertExitCode(0);

    Mail::assertQueued(BookingReminder::class, 2);
    expect($dueOne->fresh()->reminder_30m_sent_at)->not->toBeNull();
    expect($dueTwo->fresh()->reminder_30m_sent_at)->not->toBeNull();
});

it('handles double-submitted transfer confirmations and duplicate admin verifications without duplicate emails or payment records', function (): void {
    $admin = User::factory()->create(['role' => UserRole::admin]);
    $customer = User::factory()->customer()->create(['email' => 'doubleclick@example.com']);
    $barber = Barber::factory()->create();
    $service = Service::factory()->create(['price' => 15000]);

    Setting::updateOrCreate(['key' => 'payment_auto_confirm'], ['value' => 'true', 'type' => 'boolean', 'group' => 'payment']);

    $appt = Appointment::factory()->create([
        'booking_reference' => 'CC-DBL01',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'grand_total' => 15000,
        'total_amount' => 15000,
        'status' => AppointmentStatus::pending->value,
        'payment_status' => AppointmentPaymentStatus::unpaid->value,
        'deposit_paid' => false,
    ]);

    $this->actingAs($customer)->postJson('/api/v1/payments/confirm-transfer', [
        'appointment_id' => $appt->id,
        'barber_id' => $barber->id,
        'payment_method' => 'bank_transfer',
        'amount' => 15000,
        'reference' => 'DBL-REF-1',
    ])->assertOk();

    // Second rapid duplicate call must return idempotently with 1 email and 1 payment record
    $this->actingAs($customer)->postJson('/api/v1/payments/confirm-transfer', [
        'appointment_id' => $appt->id,
        'barber_id' => $barber->id,
        'payment_method' => 'bank_transfer',
        'amount' => 15000,
        'reference' => 'DBL-REF-2',
    ])->assertOk()
        ->assertJsonPath('data.payment_status', 'paid');

    expect(Payment::where('appointment_id', $appt->id)->count())->toBe(1);
    Mail::assertQueued(BookingConfirmation::class, 1);

    // Concurrent/repeated admin verification on an awaiting payment must emit strictly 1 email
    Mail::fake();
    $apptTwo = Appointment::factory()->create([
        'booking_reference' => 'CC-DBL02',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'grand_total' => 15000,
        'status' => AppointmentStatus::pending->value,
        'payment_status' => AppointmentPaymentStatus::unpaid->value,
        'deposit_paid' => false,
    ]);

    $manualPay = Payment::create([
        'appointment_id' => $apptTwo->id,
        'customer_id' => $customer->id,
        'amount' => 15000,
        'amount_minor' => 1500000,
        'currency' => 'NGN',
        'status' => PaymentStatus::awaiting_verification->value,
        'provider' => 'manual_transfer',
        'payment_method' => 'bank_transfer',
        'reference' => 'MAN-DBL-02',
    ]);

    $this->actingAs($admin)->postJson("/api/v1/admin/payments/{$manualPay->id}/verify")->assertOk();
    $this->actingAs($admin)->postJson("/api/v1/admin/payments/{$manualPay->id}/verify")->assertOk();

    Mail::assertQueued(BookingConfirmation::class, 1);
});

it('reuses failed delivery records on retry and prevents duplicate sends once delivered', function (): void {
    $customer = User::factory()->customer()->create(['email' => 'retry.client@example.com']);
    $barber = Barber::factory()->create();
    $service = Service::factory()->create(['price' => 9000]);

    $appointment = Appointment::factory()->create([
        'booking_reference' => 'CC-RETRY1',
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'deposit_paid' => true,
        'payment_status' => AppointmentPaymentStatus::paid->value,
    ]);

    $notification = Notification::create([
        'recipient_id' => $customer->id,
        'recipient_type' => 'customer',
        'type' => 'payment_verified',
        'category' => 'payment',
        'title' => 'Payment Confirmed',
        'message' => 'Test delivery retry handling',
        'related_entity_id' => $appointment->id,
        'is_read' => false,
    ]);

    $delivery = NotificationDelivery::create([
        'notification_id' => $notification->id,
        'channel' => 'email',
        'recipient_email' => $customer->email,
        'status' => 'failed',
        'attempts' => 1,
    ]);

    $runner = app(TransactionalMailRunner::class);
    $runner->send($appointment, new BookingConfirmation($appointment), 'retry email', $notification->id);

    $delivery->refresh();
    expect($delivery->status)->toBe('delivered');
    expect($delivery->attempts)->toBe(2);
    expect(NotificationDelivery::where('notification_id', $notification->id)->where('channel', 'email')->count())->toBe(1);

    // Repeating once already delivered must skip without sending again
    $runner->send($appointment, new BookingConfirmation($appointment), 'retry email', $notification->id);
    Mail::assertQueued(BookingConfirmation::class, 1);
});
