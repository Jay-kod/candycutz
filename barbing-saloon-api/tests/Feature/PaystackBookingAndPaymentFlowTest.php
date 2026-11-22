<?php

declare(strict_types=1);

use App\Domain\Booking\Actions\CreateBooking;
use App\Domain\Booking\DataObjects\BookingData;
use App\Domain\Booking\Services\AvailabilityEngine;
use App\Domain\Booking\Services\BookingService;
use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Payment\Services\PaymentConfirmer;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\Shared\Enums\UserRole;
use App\Events\PaymentConfirmed;
use App\Exceptions\BookingSlotUnavailableException;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Branch;
use App\Models\Notification;
use App\Models\NotificationBroadcast;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('payments.default', 'paystack');
    Config::set('payments.gateways.paystack.secret_key', 'sk_test_mock_secret_key_999');
    Config::set('payments.gateways.paystack.public_key', 'pk_test_mock_public_key_999');

    Http::fake([
        'https://api.paystack.co/transaction/initialize' => Http::response([
            'status' => true,
            'message' => 'Authorization URL created',
            'data' => [
                'authorization_url' => 'https://checkout.paystack.com/mock_paystack_url_999',
                'access_code' => 'mock_access_code_999',
                'reference' => 'CC-TEST-PAYSTACK-999',
            ],
        ], 200),
        'https://api.paystack.co/transaction/verify/*' => Http::response([
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'status' => 'success',
                'reference' => 'CC-TEST-PAYSTACK-999',
                'amount' => 500000,
                'id' => 99912345,
                'gateway_response' => 'Successful',
                'channel' => 'card',
            ],
        ], 200),
        'https://api.paystack.co/integration/payment_session_timeout' => Http::response([
            'status' => true,
            'message' => 'Payment session timeout retrieved',
            'data' => [
                'payment_session_timeout' => 30,
            ],
        ], 200),
        'https://api.paystack.co/balance' => Http::response([
            'status' => true,
            'message' => 'Balances retrieved',
            'data' => [
                ['currency' => 'NGN', 'balance' => 25000000],
            ],
        ], 200),
    ]);
});

it('allows admin to manage paystack settings and test live connection', function () {
    $admin = User::factory()->create(['role' => UserRole::admin]);
    $customer = User::factory()->create(['role' => UserRole::customer]);

    // 1. Customer is forbidden
    $this->actingAs($customer)->getJson('/api/v1/admin/paystack/settings')->assertStatus(403);

    // 2. Admin retrieves initial settings
    $res = $this->actingAs($admin)->getJson('/api/v1/admin/paystack/settings');
    $res->assertStatus(200);
    $res->assertJsonStructure(['success', 'data' => ['public_key', 'webhook_url']]);

    // 3. Admin updates settings
    $updateRes = $this->actingAs($admin)->postJson('/api/v1/admin/paystack/settings', [
        'public_key' => 'pk_test_live_key_custom_123',
        'secret_key' => 'sk_test_live_key_custom_123',
        'default_gateway' => 'paystack',
    ]);
    $updateRes->assertStatus(200);

    expect(Setting::query()->where('key', 'paystack_public_key')->value('value'))->toBe('pk_test_live_key_custom_123');
    expect(Setting::query()->where('key', 'paystack_secret_key')->value('value'))->toBe('sk_test_live_key_custom_123');
    expect(Setting::query()->where('key', 'payment_default_gateway')->value('value'))->toBe('paystack');

    // 4. Admin tests connection
    $testRes = $this->actingAs($admin)->postJson('/api/v1/admin/paystack/test-connection', [
        'secret_key' => 'sk_test_live_key_custom_123',
    ]);
    $testRes->assertStatus(200);
    $testRes->assertJson([
        'success' => true,
        'data' => [
            'connected' => true,
            'mode' => 'test',
        ],
    ]);
});

it('orchestrates end-to-end booking, paystack checkout, verification, notification, and barber dashboard integration', function () {
    $branch = Branch::factory()->create(['is_active' => true]);
    $barberUser = User::factory()->create(['name' => 'Tunde Master Barber', 'role' => UserRole::barber]);
    $barber = Barber::factory()->create([
        'user_id' => $barberUser->id,
        'branch_id' => $branch->id,
        'status' => 'active',
        'is_available' => true,
    ]);

    // Set barber working hours for today (Monday-Sunday)
    $today = Carbon::today();
    $dayOfWeek = (int) $today->dayOfWeekIso % 7;
    WorkingHour::create([
        'barber_id' => $barber->id,
        'day_of_week' => $dayOfWeek,
        'open_time' => '08:00:00',
        'close_time' => '20:00:00',
        'is_closed' => false,
    ]);

    $service = Service::factory()->create([
        'name' => 'Signature Fade & Beard Sculpt',
        'price' => 500000, // 5000 NGN in kobo
        'duration_minutes' => 45,
        'is_active' => true,
    ]);

    $customer = User::factory()->create([
        'name' => 'Chidi Okonkwo',
        'real_name' => 'Chidi Okonkwo',
        'email' => 'chidi.customer@example.com',
        'role' => UserRole::customer,
    ]);

    // Step 1: Customer creates booking
    $bookingData = new BookingData(
        serviceId: $service->id,
        barberId: $barber->id,
        appointmentDate: $today->toDateString(),
        appointmentTime: '14:00',
        appointmentType: 'in_shop',
        destinationAddress: null,
        paymentMethod: 'paystack',
        notes: 'VIP customer cut',
        clientName: $customer->name,
        clientPhone: '08099887766'
    );

    $bookingAction = app(CreateBooking::class);
    $appointment = $bookingAction->execute($customer, $bookingData);

    expect($appointment->status->value)->toBe(AppointmentStatus::pending->value);
    expect($appointment->barber_id)->toBe($barber->id);
    expect($appointment->service_id)->toBe($service->id);

    // Step 2: Slot Locking Verification — another customer cannot book overlapping time
    $customer2 = User::factory()->create(['role' => UserRole::customer]);
    $overlappingData = new BookingData(
        serviceId: $service->id,
        barberId: $barber->id,
        appointmentDate: $today->toDateString(),
        appointmentTime: '14:15', // Overlaps with 14:00 - 14:45
        appointmentType: 'in_shop',
        destinationAddress: null,
        paymentMethod: 'paystack',
        notes: null,
        clientName: 'Second Customer',
        clientPhone: '08011223344'
    );

    expect(fn() => $bookingAction->execute($customer2, $overlappingData))
        ->toThrow(BookingSlotUnavailableException::class);

    // Verify slot is removed from availability engine
    $availabilityEngine = app(AvailabilityEngine::class);
    $availableSlots = $availabilityEngine->calculateAvailableSlots($today, $barber->id, 45, false, $branch->id);
    expect($availableSlots)->not->toContain('14:00');

    // Step 3: Customer initiates Paystack checkout
    $checkoutRes = $this->actingAs($customer)->postJson('/api/v1/payments/checkout', [
        'appointment_id' => $appointment->id,
        'payment_method' => 'paystack',
        'client' => 'web',
    ]);

    $checkoutRes->assertStatus(200);
    $checkoutData = $checkoutRes->json('data.checkout');
    expect($checkoutData['authorization_url'])->toBe('https://checkout.paystack.com/mock_paystack_url_999');
    expect($checkoutData['access_code'])->toBe('mock_access_code_999');

    $reference = $checkoutData['reference'];

    // Step 4: Verification call (as customer completes popup or redirects back)
    $verifyRes = $this->actingAs($customer)->getJson("/api/v1/customer/payments/{$reference}");
    $verifyRes->assertStatus(200);
    $verifyRes->assertJson([
        'success' => true,
        'data' => [
            'status' => 'successful',
            'appointment' => [
                'id' => $appointment->id,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'deposit_paid' => true,
            ],
        ],
    ]);

    // Step 5: Database asserts — appointment confirmed and deposit paid
    $appointment->refresh();
    expect($appointment->status->value)->toBe('confirmed');
    expect((bool) $appointment->deposit_paid)->toBeTrue();
    expect($appointment->payment_status->value)->toBe('paid');

    // Step 6: Multi-Role Notifications dispatched
    // 6a: Customer notification
    $custNotif = Notification::where('recipient_id', $customer->id)
        ->where('type', 'payment_verified')
        ->first();
    expect($custNotif)->not->toBeNull();
    expect($custNotif->title)->toContain('Payment Confirmed');

    // 6b: Barber notification
    $barberNotif = Notification::where('recipient_id', $barberUser->id)
        ->where('type', 'payment_verified')
        ->first();
    expect($barberNotif)->not->toBeNull();
    expect($barberNotif->message)->toContain($customer->name);

    // 6c: Admin broadcast notification
    $adminBroadcast = Notification::where('recipient_type', 'all_admins')
        ->where('type', 'payment_verified')
        ->first();
    expect($adminBroadcast)->not->toBeNull();

    // Step 7: Barber Dashboard reflection
    $barberDashRes = $this->actingAs($barberUser)->getJson('/api/v1/barber/dashboard');
    $barberDashRes->assertStatus(200);
    $todayAppts = $barberDashRes->json('data.today_appointments');

    // The booked service appears on the barber's dashboard!
    $foundBooking = collect($todayAppts)->firstWhere('id', $appointment->id);
    expect($foundBooking)->not->toBeNull();
    expect($foundBooking['client_name'])->toBe($customer->name);
    expect($foundBooking['service']['name'])->toBe($service->name);
    expect($foundBooking['status'])->toBe('confirmed');

    // Step 8: Barber Appointments list reflection
    $barberApptsRes = $this->actingAs($barberUser)->getJson('/api/v1/barber/appointments');
    $barberApptsRes->assertStatus(200);
    $allBarberAppts = $barberApptsRes->json('data.data') ?: $barberApptsRes->json('data');
    $foundInList = collect($allBarberAppts)->firstWhere('id', $appointment->id);
    expect($foundInList)->not->toBeNull();
    expect($foundInList['status'])->toBe('confirmed');
});

it('releases unpaid expired bookings and frees the reserved slot', function () {
    $branch = Branch::factory()->create(['is_active' => true]);
    $barber = Barber::factory()->create(['branch_id' => $branch->id, 'status' => 'active', 'is_available' => true]);
    $service = Service::factory()->create(['duration_minutes' => 30]);
    $customer = User::factory()->create(['role' => UserRole::customer]);

    // An unpaid booking created 45 minutes ago
    $staleAppointment = Appointment::factory()->create([
        'branch_id' => $branch->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'customer_id' => $customer->id,
        'status' => AppointmentStatus::pending->value,
        'payment_status' => 'unpaid',
        'deposit_paid' => false,
        'appointment_date' => now()->addDays(2)->toDateString(),
        'appointment_time' => '11:00:00',
        'created_at' => now()->subMinutes(45),
        'payment_due_at' => null,
    ]);

    $this->artisan('bookings:release-unpaid --minutes=30')
        ->expectsOutputToContain('Release complete')
        ->assertExitCode(0);

    $staleAppointment->refresh();
    expect($staleAppointment->status->value)->toBe(AppointmentStatus::cancelled->value);
    expect($staleAppointment->cancellation_reason)->toContain('Payment window expired');
});
