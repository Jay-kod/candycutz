<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Notification\NotificationDispatcher;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Notification;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTriadTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $barberUser;
    private Barber $barber;
    private User $admin;
    private Service $service;
    private Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Customer
        $this->customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'Alice Customer',
            'email' => 'alice@test.com',
        ]);

        // 2. Setup Barber
        $this->barberUser = User::factory()->create([
            'role' => 'barber',
            'name' => 'Bob Barber',
            'email' => 'bob@test.com',
        ]);

        $this->barber = Barber::create([
            'user_id' => $this->barberUser->id,
            'is_active' => true,
        ]);

        // 3. Setup Admin
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Carol Admin',
            'email' => 'admin@test.com',
        ]);

        // 4. Setup Service
        $this->service = Service::factory()->create([
            'name' => 'VIP Fade',
            'price' => 15000,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);

        // 5. Setup Appointment
        $this->appointment = Appointment::create([
            'booking_reference' => 'CC-TEST01',
            'branch_id' => 1,
            'customer_id' => $this->customer->id,
            'barber_id' => $this->barber->id,
            'service_id' => $this->service->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '14:00',
            'end_time' => '14:45',
            'status' => 'pending',
            'total_price' => 15000,
            'total_amount' => 15000,
            'grand_total' => 15000,
            'deposit_paid' => false,
            'client_name' => 'Alice Customer',
            'client_phone' => '08012345678',
            'client_email' => 'alice@test.com',
            'source' => 'web',
        ]);
    }

    public function test_booking_created_dispatches_to_customer_barber_and_admin(): void
    {
        NotificationDispatcher::bookingCreated($this->appointment, $this->customer);

        // Customer notification
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->customer->id,
            'recipient_type' => 'customer',
            'type' => 'booking_created',
            'related_entity_id' => $this->appointment->id,
        ]);

        // Barber notification
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->barberUser->id,
            'recipient_type' => 'barber',
            'type' => 'booking_created',
            'related_entity_id' => $this->appointment->id,
        ]);

        // Admin broadcast
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => null,
            'recipient_type' => 'all_admins',
            'type' => 'booking_created',
            'related_entity_id' => $this->appointment->id,
        ]);
    }

    public function test_booking_approved_dispatches_to_customer_barber_and_admin(): void
    {
        NotificationDispatcher::bookingApproved($this->appointment, $this->barberUser);

        // Customer
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->customer->id,
            'recipient_type' => 'customer',
            'type' => 'booking_approved',
        ]);

        // Barber
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->barberUser->id,
            'recipient_type' => 'barber',
            'type' => 'booking_approved',
        ]);

        // Admin
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => null,
            'recipient_type' => 'all_admins',
            'type' => 'booking_approved',
        ]);
    }

    public function test_booking_cancelled_dispatches_to_customer_barber_and_admin(): void
    {
        $this->appointment->update(['cancellation_reason' => 'Schedule conflict']);
        NotificationDispatcher::bookingCancelled($this->appointment, $this->customer);

        // Customer
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->customer->id,
            'recipient_type' => 'customer',
            'type' => 'booking_cancelled',
        ]);

        // Barber
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->barberUser->id,
            'recipient_type' => 'barber',
            'type' => 'booking_cancelled',
        ]);

        // Admin
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => null,
            'recipient_type' => 'all_admins',
            'type' => 'booking_cancelled',
        ]);
    }

    public function test_booking_completed_dispatches_to_customer_barber_and_admin(): void
    {
        NotificationDispatcher::bookingCompleted($this->appointment, $this->barberUser);

        // Customer
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->customer->id,
            'recipient_type' => 'customer',
            'type' => 'booking_completed',
        ]);

        // Barber
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->barberUser->id,
            'recipient_type' => 'barber',
            'type' => 'booking_completed',
        ]);

        // Admin
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => null,
            'recipient_type' => 'all_admins',
            'type' => 'booking_completed',
        ]);
    }

    public function test_payment_verified_dispatches_to_customer_barber_and_admin(): void
    {
        NotificationDispatcher::paymentVerified($this->appointment);

        // Customer
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->customer->id,
            'recipient_type' => 'customer',
            'type' => 'payment_verified',
        ]);

        // Barber
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->barberUser->id,
            'recipient_type' => 'barber',
            'type' => 'payment_verified',
        ]);

        // Admin
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => null,
            'recipient_type' => 'all_admins',
            'type' => 'payment_verified',
        ]);
    }

    public function test_payment_failed_dispatches_to_customer_barber_and_admin(): void
    {
        NotificationDispatcher::paymentFailed($this->appointment, 'Card declined');

        // Customer
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->customer->id,
            'recipient_type' => 'customer',
            'type' => 'payment_failed',
        ]);

        // Barber
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->barberUser->id,
            'recipient_type' => 'barber',
            'type' => 'payment_failed',
        ]);

        // Admin
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => null,
            'recipient_type' => 'all_admins',
            'type' => 'payment_failed',
        ]);
    }

    public function test_barber_retrieves_notifications_and_unread_count(): void
    {
        NotificationDispatcher::bookingCreated($this->appointment, $this->customer);

        // Barber endpoint check
        $res = $this->actingAs($this->barberUser)->getJson('/api/v1/notifications');
        $res->assertStatus(200)
            ->assertJsonPath('success', true);

        $data = $res->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals('booking_created', $data[0]['type']);

        // Barber unread count check
        $countRes = $this->actingAs($this->barberUser)->getJson('/api/v1/notifications/unread-count');
        $countRes->assertStatus(200)
            ->assertJsonPath('data.count', 1);

        // Mark as read
        $notifId = $data[0]['id'];
        $readRes = $this->actingAs($this->barberUser)->patchJson("/api/v1/notifications/{$notifId}/read");
        $readRes->assertStatus(200);

        // Unread count should now be 0
        $countRes2 = $this->actingAs($this->barberUser)->getJson('/api/v1/notifications/unread-count');
        $countRes2->assertStatus(200)
            ->assertJsonPath('data.count', 0);
    }

    public function test_admin_retrieves_broadcast_notifications(): void
    {
        NotificationDispatcher::bookingCreated($this->appointment, $this->customer);

        // Admin endpoint check
        $res = $this->actingAs($this->admin)->getJson('/api/v1/notifications');
        $res->assertStatus(200)
            ->assertJsonPath('success', true);

        $data = $res->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals('all_admins', $data[0]['recipient_type']);
        $this->assertEquals('booking_created', $data[0]['type']);

        // Type filter ?type=booking matches booking_created
        $filterRes = $this->actingAs($this->admin)->getJson('/api/v1/notifications?type=booking');
        $filterRes->assertStatus(200);
        $this->assertNotEmpty($filterRes->json('data'));
    }
}
