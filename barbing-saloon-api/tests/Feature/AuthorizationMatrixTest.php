<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceZone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $business = Business::factory()->create(['id' => 1]);
    Branch::factory()->create([
        'id' => 1,
        'business_id' => $business->id,
        'is_active' => true,
    ]);

    $category = ServiceCategory::factory()->create(['id' => 1]);
    Service::factory()->create([
        'id' => 1,
        'branch_id' => 1,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    ServiceZone::factory()->create([
        'id' => 1,
        'branch_id' => 1,
        'is_active' => true,
    ]);
});

it('enforces role gate matrix on guest, customer, barber, admin, and superadmin', function () {
    $customer = User::factory()->customer()->create();
    $barberUser = User::factory()->barber()->create();
    $admin = User::factory()->admin()->create();
    $superAdmin = User::factory()->superAdmin()->create();

    // 1. Guest probes
    $this->getJson('/api/v1/customer/profile')->assertUnauthorized();
    $this->getJson('/api/v1/barber/dashboard')->assertUnauthorized();
    $this->getJson('/api/v1/admin/dashboard')->assertUnauthorized();
    $this->getJson('/api/v1/super-admin/dashboard')->assertUnauthorized();

    // 2. Customer probes
    $this->actingAs($customer)
        ->getJson('/api/v1/barber/dashboard')
        ->assertForbidden();
    $this->actingAs($customer)
        ->getJson('/api/v1/admin/dashboard')
        ->assertForbidden();
    $this->actingAs($customer)
        ->getJson('/api/v1/super-admin/dashboard')
        ->assertForbidden();

    // 3. Barber probes
    $this->actingAs($barberUser)
        ->getJson('/api/v1/admin/dashboard')
        ->assertForbidden();
    $this->actingAs($barberUser)
        ->getJson('/api/v1/super-admin/dashboard')
        ->assertForbidden();

    // 4. Admin probes
    $this->actingAs($admin)
        ->getJson('/api/v1/admin/dashboard')
        ->assertOk();
    $this->actingAs($admin)
        ->getJson('/api/v1/super-admin/dashboard')
        ->assertForbidden();

    // 5. Super Admin probes
    $this->actingAs($superAdmin)
        ->getJson('/api/v1/admin/dashboard')
        ->assertOk();
    $this->actingAs($superAdmin)
        ->getJson('/api/v1/super-admin/dashboard')
        ->assertOk();
});

it('prohibits cross-tenant access between customers for appointments and receipts', function () {
    $customerA = User::factory()->customer()->create(['email' => 'customerA@test.com']);
    $customerB = User::factory()->customer()->create(['email' => 'customerB@test.com']);

    $appointmentB = Appointment::factory()
        ->state([
            'customer_id' => $customerB->id,
            'client_name' => $customerB->name,
            'client_email' => $customerB->email,
            'client_phone' => $customerB->phone,
        ])
        ->create();

    // Customer A attempting to access or cancel Customer B's appointment
    $response = $this->actingAs($customerA)
        ->postJson("/api/v1/appointments/{$appointmentB->id}/cancel", [
            'reason' => 'Unauthorized cancellation attempt',
        ]);

    expect($response->status())->toBe(403);
});

it('immediately rejects requests from suspended or deactivated users with AUTH_ACCOUNT_SUSPENDED', function () {
    $suspendedUser = User::factory()->customer()->create([
        'is_active' => false,
        'status' => 'suspended',
    ]);

    $response = $this->actingAs($suspendedUser)
        ->getJson('/api/v1/customer/profile');

    $response->assertForbidden();
    expect($response->json('error.code'))->toBe('AUTH_ACCOUNT_SUSPENDED');
    expect($response->json('error.action'))->toBe('logout');
});
