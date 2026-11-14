<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Booking\Services\BookingService;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Notification;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApprovalNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_approving_booking_only_notifies_involved_customer_and_barber(): void
    {
        // 1. Arrange: Admin, Barber, Customer A (involved), Customer B (unrelated)
        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Boss',
            'email' => 'admin@candycutz.test',
        ]);

        $barberUser = User::factory()->create([
            'role' => 'barber',
            'name' => 'Barber John',
            'email' => 'barber@candycutz.test',
        ]);

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'is_active' => true,
        ]);

        $customerA = User::factory()->create([
            'role' => 'customer',
            'name' => 'Customer Alice',
            'email' => 'alice@candycutz.test',
        ]);

        $customerB = User::factory()->create([
            'role' => 'customer',
            'name' => 'Customer Bob',
            'email' => 'bob@candycutz.test',
        ]);

        $service = Service::factory()->create([
            'name' => 'Signature Haircut',
            'price' => 5000,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        $appointment = Appointment::create([
            'booking_reference' => 'CC-ALICE-01',
            'branch_id' => 1,
            'customer_id' => $customerA->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'end_time' => '10:30',
            'status' => AppointmentStatus::pending->value,
            'total_price' => 5000,
            'total_amount' => 5000,
            'grand_total' => 5000,
            'deposit_paid' => true,
            'client_name' => 'Customer Alice',
            'client_phone' => '08011112222',
            'client_email' => 'alice@candycutz.test',
            'source' => 'web',
        ]);

        // 2. Act: Admin approves Customer A's appointment via API endpoint
        $response = $this->actingAs($admin)->patchJson("/api/v1/admin/appointments/{$appointment->id}/approve");
        $response->assertOk();

        // 3. Assert: Database records
        // Customer A must have received booking_approved
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $customerA->id,
            'type' => 'booking_approved',
        ]);

        // Barber must have received booking_approved
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $barberUser->id,
            'type' => 'booking_approved',
        ]);

        // 4. Assert: Query endpoints for Customer A
        $resCustomerA = $this->actingAs($customerA)->getJson('/api/v1/notifications');
        $resCustomerA->assertOk();
        $notifsCustomerA = $resCustomerA->json('data');
        $this->assertCount(1, $notifsCustomerA);
        $this->assertEquals('booking_approved', $notifsCustomerA[0]['type']);
        $this->assertEquals($customerA->id, $notifsCustomerA[0]['recipient_id']);

        // 5. Assert: Query endpoints for Customer B (unrelated customer)
        $resCustomerB = $this->actingAs($customerB)->getJson('/api/v1/notifications');
        $resCustomerB->assertOk();
        $notifsCustomerB = $resCustomerB->json('data');
        $this->assertCount(0, $notifsCustomerB, 'Unrelated customer must NOT see Customer A booking approval notification!');

        $unreadCountCustomerB = $this->actingAs($customerB)->getJson('/api/v1/notifications/unread-count');
        $unreadCountCustomerB->assertOk();
        $this->assertEquals(0, $unreadCountCustomerB->json('data.count'), 'Unrelated customer unread count must be 0!');
    }
}
