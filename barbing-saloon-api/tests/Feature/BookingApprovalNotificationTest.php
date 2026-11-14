<?php

declare(strict_types=1);

namespace Tests\Feature;

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

    private User $admin;
    private User $barberUser;
    private Barber $barber;
    private User $customerA;
    private User $customerB;
    private Service $service;
    private Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Boss',
            'email' => 'admin@candycutz.test',
        ]);

        $this->barberUser = User::factory()->create([
            'role' => 'barber',
            'name' => 'Barber John',
            'email' => 'barber@candycutz.test',
        ]);

        $this->barber = Barber::create([
            'user_id' => $this->barberUser->id,
            'is_active' => true,
        ]);

        $this->customerA = User::factory()->create([
            'role' => 'customer',
            'name' => 'Customer Alice',
            'email' => 'alice@candycutz.test',
        ]);

        $this->customerB = User::factory()->create([
            'role' => 'customer',
            'name' => 'Customer Bob',
            'email' => 'bob@candycutz.test',
        ]);

        $this->service = Service::factory()->create([
            'name' => 'Signature Haircut',
            'price' => 5000,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        $this->appointment = Appointment::create([
            'booking_reference' => 'CC-ALICE-01',
            'branch_id' => 1,
            'customer_id' => $this->customerA->id,
            'barber_id' => $this->barber->id,
            'service_id' => $this->service->id,
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
    }

    public function test_admin_approving_booking_only_notifies_involved_customer_and_barber(): void
    {
        // Act: Admin approves Customer A's appointment via API
        $response = $this->actingAs($this->admin)->patchJson("/api/v1/admin/appointments/{$this->appointment->id}/approve");
        $response->assertOk();

        // Assert: Database records
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->customerA->id,
            'recipient_type' => 'customer',
            'type' => 'booking_approved',
        ]);

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->barberUser->id,
            'recipient_type' => 'barber',
            'type' => 'booking_approved',
        ]);

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => null,
            'recipient_type' => 'all_admins',
            'type' => 'booking_approved',
        ]);

        // Customer A: sees their 1 booking approval notification
        $resCustomerA = $this->actingAs($this->customerA)->getJson('/api/v1/notifications');
        $resCustomerA->assertOk();
        $notifsCustomerA = $resCustomerA->json('data');
        $this->assertCount(1, $notifsCustomerA);
        $this->assertEquals('booking_approved', $notifsCustomerA[0]['type']);
        $this->assertEquals($this->customerA->id, $notifsCustomerA[0]['recipient_id']);

        $unreadCustomerA = $this->actingAs($this->customerA)->getJson('/api/v1/notifications/unread-count');
        $unreadCustomerA->assertOk();
        $this->assertEquals(1, $unreadCustomerA->json('data.count'));

        // Barber: sees their 1 booking approval notification
        $resBarber = $this->actingAs($this->barberUser)->getJson('/api/v1/notifications');
        $resBarber->assertOk();
        $notifsBarber = $resBarber->json('data');
        $this->assertCount(1, $notifsBarber);
        $this->assertEquals('booking_approved', $notifsBarber[0]['type']);
        $this->assertEquals($this->barberUser->id, $notifsBarber[0]['recipient_id']);

        // Unrelated Customer B: sees 0 notifications!
        $resCustomerB = $this->actingAs($this->customerB)->getJson('/api/v1/notifications');
        $resCustomerB->assertOk();
        $notifsCustomerB = $resCustomerB->json('data');
        $this->assertCount(0, $notifsCustomerB, 'Unrelated customer must NOT see Customer A booking approval!');

        $unreadCustomerB = $this->actingAs($this->customerB)->getJson('/api/v1/notifications/unread-count');
        $unreadCustomerB->assertOk();
        $this->assertEquals(0, $unreadCustomerB->json('data.count'), 'Unrelated customer unread count must be 0!');
    }

    public function test_guest_walk_in_approval_does_not_leak_to_customers(): void
    {
        // Arrange: Walk-in appointment without customer_id
        $walkIn = Appointment::create([
            'booking_reference' => 'CC-WALKIN-99',
            'branch_id' => 1,
            'customer_id' => null,
            'barber_id' => $this->barber->id,
            'service_id' => $this->service->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '11:00',
            'end_time' => '11:30',
            'status' => AppointmentStatus::pending->value,
            'total_price' => 5000,
            'total_amount' => 5000,
            'grand_total' => 5000,
            'deposit_paid' => true,
            'client_name' => 'Walk-In Guest',
            'client_phone' => '08099998888',
            'client_email' => 'walkin@example.com',
            'source' => 'walk_in',
        ]);

        // Act: Admin approves walk-in
        $this->actingAs($this->admin)->patchJson("/api/v1/admin/appointments/{$walkIn->id}/approve")
            ->assertOk();

        // Customer A and Customer B must see 0 notifications
        $this->assertCount(0, $this->actingAs($this->customerA)->getJson('/api/v1/notifications')->json('data'));
        $this->assertCount(0, $this->actingAs($this->customerB)->getJson('/api/v1/notifications')->json('data'));

        // Barber receives the notification
        $this->assertCount(1, $this->actingAs($this->barberUser)->getJson('/api/v1/notifications')->json('data'));
    }

    public function test_unrelated_customer_cannot_read_or_delete_other_customer_notification(): void
    {
        // Create a notification for Customer A
        $notif = Notification::create([
            'sender_id' => $this->admin->id,
            'recipient_type' => 'customer',
            'recipient_id' => $this->customerA->id,
            'type' => 'booking_approved',
            'title' => 'Your Cut is Approved',
            'message' => 'See you tomorrow at 10am',
            'is_read' => false,
        ]);

        // Customer B attempts IDOR read -> 404
        $resRead = $this->actingAs($this->customerB)->patchJson("/api/v1/notifications/{$notif->id}/read");
        $resRead->assertStatus(404);
        $this->assertFalse($notif->fresh()->is_read);

        // Customer B attempts IDOR delete -> 404
        $resDel = $this->actingAs($this->customerB)->deleteJson("/api/v1/notifications/{$notif->id}");
        $resDel->assertStatus(404);
        $this->assertDatabaseHas('notifications', ['id' => $notif->id]);

        // Customer A legitimately marks it as read -> 200
        $resAuthRead = $this->actingAs($this->customerA)->patchJson("/api/v1/notifications/{$notif->id}/read");
        $resAuthRead->assertOk();
        $this->assertTrue($notif->fresh()->is_read);

        // Customer A legitimately deletes it -> 200
        $resAuthDel = $this->actingAs($this->customerA)->deleteJson("/api/v1/notifications/{$notif->id}");
        $resAuthDel->assertOk();
        $this->assertDatabaseMissing('notifications', ['id' => $notif->id]);
    }

    public function test_system_announcement_broadcast_is_received_by_all_customers(): void
    {
        // Admin creates a shop announcement broadcast
        $this->actingAs($this->admin)->postJson('/api/v1/notifications', [
            'title' => 'Holiday Hours Announcement',
            'message' => 'CandyCutz will be open extended hours this weekend.',
            'type' => 'announcement',
            'recipient_type' => 'all_customers',
        ])->assertStatus(201);

        // Both Customer A and Customer B see the announcement
        $resA = $this->actingAs($this->customerA)->getJson('/api/v1/notifications');
        $resA->assertOk();
        $this->assertCount(1, $resA->json('data'));
        $this->assertEquals('Holiday Hours Announcement', $resA->json('data.0.title'));

        $resB = $this->actingAs($this->customerB)->getJson('/api/v1/notifications');
        $resB->assertOk();
        $this->assertCount(1, $resB->json('data'));
        $this->assertEquals('Holiday Hours Announcement', $resB->json('data.0.title'));
    }

    public function test_customer_cannot_send_broadcast_notifications(): void
    {
        // Customer attempts to broadcast to all customers -> 403 Forbidden
        $response = $this->actingAs($this->customerA)->postJson('/api/v1/notifications', [
            'title' => 'Spam broadcast',
            'message' => 'Spamming all customers',
            'recipient_type' => 'all_customers',
        ]);

        $response->assertStatus(403);
    }
}
