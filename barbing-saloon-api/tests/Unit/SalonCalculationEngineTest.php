<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\System\Services\SalonCalculationEngine;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalonCalculationEngineTest extends TestCase
{
    use RefreshDatabase;

    private SalonCalculationEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new SalonCalculationEngine();
    }

    public function test_it_computes_exact_revenue_from_services_carried_out_and_confirmed(): void
    {
        $today = Carbon::parse('2026-10-24');

        $category = ServiceCategory::create([
            'name' => 'Haircut',
            'slug' => 'haircut',
            'display_order' => 1,
        ]);

        $service1 = Service::create([
            'name' => 'Premium Fade',
            'slug' => 'premium-fade',
            'description' => 'Detailed skin fade',
            'price' => 2000,
            'duration_minutes' => 45,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $service2 = Service::create([
            'name' => 'Dye Complete',
            'slug' => 'dye-complete',
            'description' => 'Complete hair color',
            'price' => 8000,
            'duration_minutes' => 60,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $user = User::factory()->create(['name' => 'Marcus Barber', 'role' => 'barber']);
        $barber = Barber::create([
            'user_id' => $user->id,
            'is_available' => true,
            'rating' => 4.9,
        ]);

        $customer = User::factory()->create(['name' => 'Client One', 'role' => 'customer']);

        // 1. Confirmed upcoming appointment for service 1 (₦2,000)
        Appointment::create([
            'booking_reference' => 'CC-TEST-1',
            'customer_id' => $customer->id,
            'client_name' => $customer->name,
            'client_phone' => '08012345678',
            'barber_id' => $barber->id,
            'service_id' => $service1->id,
            'appointment_date' => '2026-10-24',
            'appointment_time' => '10:00:00',
            'status' => AppointmentStatus::confirmed,
            'total_price' => 2000,
            'total_amount' => 2000,
        ]);

        // 2. Completed appointment carried out for service 2 (₦8,000)
        Appointment::create([
            'booking_reference' => 'CC-TEST-2',
            'customer_id' => $customer->id,
            'client_name' => $customer->name,
            'client_phone' => '08012345678',
            'barber_id' => $barber->id,
            'service_id' => $service2->id,
            'appointment_date' => '2026-10-24',
            'appointment_time' => '14:00:00',
            'status' => AppointmentStatus::completed,
            'total_price' => 8000,
            'total_amount' => 8000,
        ]);

        $metrics = $this->engine->computeSummaryMetrics($today);

        // Verify Realized (Carried Out) Revenue matches service carried out exactly: ₦8,000
        $this->assertEquals(8000.0, $metrics['revenue']['realized_revenue']);

        // Verify Booked (Confirmed) Revenue: ₦2,000
        $this->assertEquals(2000.0, $metrics['revenue']['booked_revenue']);

        // Verify Total Revenue: ₦10,000
        $this->assertEquals(10000.0, $metrics['revenue']['total_revenue']);
        $this->assertEquals(10000.0, $metrics['revenue']['today_revenue']);

        // Verify Appointment Counts
        $this->assertEquals(2, $metrics['appointments']['total']);
        $this->assertEquals(1, $metrics['appointments']['completed']);
        $this->assertEquals(1, $metrics['appointments']['confirmed']);
        $this->assertEquals(50, $metrics['appointments']['completion_rate']);

        // Verify Average Ticket divides realized revenue by completed appointments
        $this->assertEquals(8000.0, $metrics['appointments']['average_ticket']);
    }

    public function test_it_protects_against_overinflated_or_corrupted_prices_by_normalizing_to_service_price(): void
    {
        $today = Carbon::parse('2026-10-24');

        $category = ServiceCategory::create([
            'name' => 'Haircut',
            'slug' => 'haircut',
            'display_order' => 1,
        ]);

        $service = Service::create([
            'name' => 'Standard Cut',
            'slug' => 'standard-cut',
            'description' => 'Standard haircut',
            'price' => 2500,
            'duration_minutes' => 30,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $user = User::factory()->create(['role' => 'barber']);
        $barber = Barber::create(['user_id' => $user->id]);
        $customer = User::factory()->create(['role' => 'customer']);

        // Appointment with corrupted 40,000,000 price in DB
        Appointment::create([
            'booking_reference' => 'CC-CORRUPT-1',
            'customer_id' => $customer->id,
            'client_name' => $customer->name,
            'client_phone' => '08012345678',
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-10-24',
            'appointment_time' => '11:00:00',
            'status' => AppointmentStatus::completed,
            'total_price' => 40000000, // 40 million corrupted multiplier
            'total_amount' => 40000000,
        ]);

        $metrics = $this->engine->computeSummaryMetrics($today);

        // Should automatically normalize 40000000 / 10000 = 4000 (or fall back to service canonical price)
        $this->assertLessThanOrEqual(4000.0, $metrics['revenue']['total_revenue']);
        $this->assertGreaterThanOrEqual(2500.0, $metrics['revenue']['total_revenue']);
    }

    public function test_it_computes_top_services_and_top_barbers_accurately(): void
    {
        $category = ServiceCategory::create(['name' => 'Style', 'slug' => 'style', 'display_order' => 1]);
        $service = Service::create([
            'name' => 'Gold Combo',
            'slug' => 'gold-combo',
            'description' => 'Luxury grooming combo',
            'price' => 6500,
            'duration_minutes' => 75,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $user = User::factory()->create(['name' => 'Alex Master', 'role' => 'barber']);
        $barber = Barber::create(['user_id' => $user->id, 'rating' => 4.95]);
        $customer = User::factory()->create(['role' => 'customer']);

        Appointment::create([
            'booking_reference' => 'CC-TOP-1',
            'customer_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-10-24',
            'appointment_time' => '15:00:00',
            'status' => AppointmentStatus::completed,
            'total_price' => 6500,
            'total_amount' => 6500,
        ]);

        $topServices = $this->engine->computeTopServices(5);
        $topBarbers = $this->engine->computeTopBarbers(5);

        $this->assertNotEmpty($topServices);
        $this->assertEquals('Gold Combo', $topServices[0]['name']);
        $this->assertEquals(1, $topServices[0]['bookings']);
        $this->assertEquals(6500.0, $topServices[0]['revenue']);

        $this->assertNotEmpty($topBarbers);
        $this->assertEquals('Alex Master', $topBarbers[0]['name']);
        $this->assertEquals(1, $topBarbers[0]['bookings']);
        $this->assertEquals(6500.0, $topBarbers[0]['revenue']);
    }
}
