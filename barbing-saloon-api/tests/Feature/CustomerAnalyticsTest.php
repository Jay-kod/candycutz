<?php

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('returns customer-scoped analytics in the shape used by the dashboard', function () {
    $customer = User::factory()->customer()->create();
    $otherCustomer = User::factory()->customer()->create();
    $service = Service::factory()->create(['name' => 'Skin Fade']);

    Appointment::factory()->create([
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::completed,
        'appointment_date' => today()->subDays(2)->toDateString(),
        'total_price' => 250000,
    ]);
    Appointment::factory()->create([
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'status' => AppointmentStatus::pending,
        'appointment_date' => today()->subDays(3)->toDateString(),
        'total_price' => 999000,
    ]);
    Appointment::factory()->create([
        'customer_id' => $customer->id,
        'status' => AppointmentStatus::completed,
        'appointment_date' => today()->subDays(60)->toDateString(),
        'total_price' => 500000,
    ]);
    Appointment::factory()->create([
        'customer_id' => $otherCustomer->id,
        'status' => AppointmentStatus::completed,
        'appointment_date' => today()->subDays(2)->toDateString(),
        'total_price' => 999000,
    ]);

    DB::table('wishlists')->insert([
        [
            'customer_id' => $customer->id,
            'item_type' => 'service',
            'item_id' => $service->id,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'customer_id' => $customer->id,
            'item_type' => 'gallery',
            'item_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'customer_id' => $otherCustomer->id,
            'item_type' => 'service',
            'item_id' => $service->id,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
    Testimonial::factory()->create(['customer_id' => $customer->id]);
    Testimonial::factory()->create(['customer_id' => $otherCustomer->id]);

    $response = $this->actingAs($customer)
        ->getJson('/api/v1/customer/analytics?range=30d')
        ->assertOk()
        ->assertJsonPath('data.status_breakdown.completed', 1)
        ->assertJsonPath('data.status_breakdown.pending', 1)
        ->assertJsonPath('data.status_breakdown.cancelled', 0)
        ->assertJsonPath('data.spending_summary.total_spent', 2500)
        ->assertJsonPath('data.spending_summary.avg_spent', 2500)
        ->assertJsonPath('data.spending_summary.most_booked', 'Skin Fade')
        ->assertJsonPath('data.activity_timeline.wishlist_items', 2)
        ->assertJsonPath('data.activity_timeline.reviews_given', 1);

    expect(collect($response->json('data.booking_trends'))
        ->firstWhere('month', today()->subDays(2)->format('M j'))['count'])
        ->toBe(1);
});

it('applies the all-time range and rejects unsupported ranges', function () {
    $customer = User::factory()->customer()->create();

    Appointment::factory()->create([
        'customer_id' => $customer->id,
        'status' => AppointmentStatus::completed,
        'appointment_date' => today()->subDays(60)->toDateString(),
        'total_price' => 125000,
    ]);

    $this->actingAs($customer)
        ->getJson('/api/v1/customer/analytics?range=30d')
        ->assertOk()
        ->assertJsonPath('data.status_breakdown.completed', 0)
        ->assertJsonPath('data.spending_summary.total_spent', 0);

    $this->actingAs($customer)
        ->getJson('/api/v1/customer/analytics?range=all')
        ->assertOk()
        ->assertJsonPath('data.status_breakdown.completed', 1)
        ->assertJsonPath('data.spending_summary.total_spent', 1250);

    $this->actingAs($customer)
        ->getJson('/api/v1/customer/analytics?range=90d')
        ->assertUnprocessable();
});
