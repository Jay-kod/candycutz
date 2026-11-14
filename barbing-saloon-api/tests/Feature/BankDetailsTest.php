<?php

declare(strict_types=1);

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;

it('allows admin to fetch and update salon bank details in database', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $fetchRes = $this->actingAs($admin)->getJson('/api/v1/admin/bank-details');
    $fetchRes->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'bank_name',
                'account_name',
                'account_number',
                'instructions',
            ],
        ]);

    $updateRes = $this->actingAs($admin)->postJson('/api/v1/admin/bank-details', [
        'bank_name' => 'Access Bank PLC',
        'account_name' => 'Candy Cutz Deluxe Lounge',
        'account_number' => '0123456789',
        'instructions' => 'Include your Ref ID in remarks.',
    ]);

    $updateRes->assertStatus(200)
        ->assertJsonPath('data.bank_name', 'Access Bank PLC')
        ->assertJsonPath('data.account_name', 'Candy Cutz Deluxe Lounge')
        ->assertJsonPath('data.account_number', '0123456789');

    // Verify stored in DB
    expect(Setting::query()->where('key', 'bank_name')->value('value'))->toBe('Access Bank PLC')
        ->and(Setting::query()->where('key', 'account_name')->value('value'))->toBe('Candy Cutz Deluxe Lounge')
        ->and(Setting::query()->where('key', 'account_number')->value('value'))->toBe('0123456789');
});

it('exposes salon bank details via public settings endpoint', function () {
    Setting::updateOrCreate(['key' => 'bank_name'], ['value' => 'United Bank for Africa', 'group' => 'payment']);
    Setting::updateOrCreate(['key' => 'account_name'], ['value' => 'Candy Cutz Official', 'group' => 'payment']);
    Setting::updateOrCreate(['key' => 'account_number'], ['value' => '2099887766', 'group' => 'payment']);

    $res = $this->getJson('/api/v1/settings');
    $res->assertStatus(200);

    $data = $res->json('data');
    expect($data['bank_name'])->toBe('United Bank for Africa')
        ->and($data['account_name'])->toBe('Candy Cutz Official')
        ->and($data['account_number'])->toBe('2099887766');
});

it('displays updated database bank details on customer appointment and checkout', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $barber = Barber::factory()->create([
        'bank_name' => null,
        'account_name' => null,
        'account_number' => null,
    ]);
    $service = Service::factory()->create(['price' => 5000]);

    Setting::updateOrCreate(['key' => 'bank_name'], ['value' => 'First Bank of Nigeria', 'group' => 'payment']);
    Setting::updateOrCreate(['key' => 'account_name'], ['value' => 'Candy Cutz Main Account', 'group' => 'payment']);
    Setting::updateOrCreate(['key' => 'account_number'], ['value' => '3011223344', 'group' => 'payment']);

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'status' => 'pending',
    ]);

    $checkoutDetailsRes = $this->actingAs($customer)->getJson("/api/v1/customer/checkout/{$appointment->id}/payment-details");
    $checkoutDetailsRes->assertStatus(200)
        ->assertJsonPath('data.bank_name', 'First Bank of Nigeria')
        ->assertJsonPath('data.account_name', 'Candy Cutz Main Account')
        ->assertJsonPath('data.account_number', '3011223344');

    $bookingsRes = $this->actingAs($customer)->getJson('/api/v1/customer/bookings');
    $bookingsRes->assertStatus(200);
    $item = collect($bookingsRes->json('data'))->firstWhere('id', $appointment->id);
    expect($item['bank_details']['bank_name'])->toBe('First Bank of Nigeria')
        ->and($item['bank_details']['account_name'])->toBe('Candy Cutz Main Account')
        ->and($item['bank_details']['account_number'])->toBe('3011223344');
});

it('prioritizes barber personal bank details over salon default when configured', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $barberUser = User::factory()->create(['role' => 'barber']);
    $barber = Barber::factory()->create([
        'user_id' => $barberUser->id,
        'bank_name' => 'Kuda Microfinance Bank',
        'account_name' => 'John Stylist Payout',
        'account_number' => '1122334455',
    ]);
    $service = Service::factory()->create(['price' => 7000]);

    Setting::updateOrCreate(['key' => 'bank_name'], ['value' => 'Zenith Bank', 'group' => 'payment']);
    Setting::updateOrCreate(['key' => 'account_name'], ['value' => 'Salon Default', 'group' => 'payment']);
    Setting::updateOrCreate(['key' => 'account_number'], ['value' => '1000000000', 'group' => 'payment']);

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'status' => 'pending',
    ]);

    $checkoutDetailsRes = $this->actingAs($customer)->getJson("/api/v1/customer/checkout/{$appointment->id}/payment-details");
    $checkoutDetailsRes->assertStatus(200)
        ->assertJsonPath('data.bank_name', 'Kuda Microfinance Bank')
        ->assertJsonPath('data.account_name', 'John Stylist Payout')
        ->assertJsonPath('data.account_number', '1122334455');
});

it('allows barber to update payout bank details via barber account endpoint', function () {
    $barberUser = User::factory()->create(['role' => 'barber']);
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);

    $updateRes = $this->actingAs($barberUser)->postJson('/api/v1/barber/account', [
        'bank_name' => 'Moniepoint MFB',
        'account_name' => 'Barber Pro Payout',
        'account_number' => '5544332211',
    ]);

    $updateRes->assertStatus(200);

    $barber->refresh();
    expect($barber->bank_name)->toBe('Moniepoint MFB')
        ->and($barber->account_name)->toBe('Barber Pro Payout')
        ->and($barber->account_number)->toBe('5544332211');
});

