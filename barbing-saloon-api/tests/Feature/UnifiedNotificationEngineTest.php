<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Notification\NotificationDispatcher;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\Service;
use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class UnifiedNotificationEngineTest extends TestCase
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
        Queue::fake();

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'Alice Customer',
            'email' => 'alice@candycutz.test',
        ]);

        $this->barberUser = User::factory()->create([
            'role' => 'barber',
            'name' => 'Bob Barber',
            'email' => 'bob@candycutz.test',
        ]);

        $this->barber = Barber::create([
            'user_id' => $this->barberUser->id,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Carol Admin',
            'email' => 'admin@candycutz.test',
        ]);

        $this->service = Service::factory()->create([
            'name' => 'Signature Beard Trim',
            'price' => 12000,
        ]);

        $this->appointment = Appointment::create([
            'booking_reference' => 'BK-TEST-1001',
            'branch_id' => 1,
            'customer_id' => $this->customer->id,
            'barber_id' => $this->barber->id,
            'service_id' => $this->service->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '14:00',
            'end_time' => '14:45',
            'status' => 'confirmed',
            'total_price' => 12000,
            'total_amount' => 12000,
            'grand_total' => 12000,
            'deposit_paid' => false,
            'client_name' => $this->customer->name,
            'client_phone' => '+2348012345678',
            'client_email' => $this->customer->email,
            'source' => 'web',
        ]);
    }

    public function test_idempotency_prevents_duplicate_notifications(): void
    {
        $idempotencyKey = hash('sha256', 'test_event:1001');

        $notif1 = NotificationDispatcher::dispatchNotification(
            recipientId: $this->customer->id,
            type: 'booking_created',
            title: 'Booking Confirmed',
            message: 'Your booking has been confirmed.',
            relatedEntityId: $this->appointment->id,
            idempotencyKey: $idempotencyKey
        );

        $notif2 = NotificationDispatcher::dispatchNotification(
            recipientId: $this->customer->id,
            type: 'booking_created',
            title: 'Booking Confirmed',
            message: 'Your booking has been confirmed.',
            relatedEntityId: $this->appointment->id,
            idempotencyKey: $idempotencyKey
        );

        expect($notif1)->not->toBeNull();
        expect($notif2)->not->toBeNull();
        expect($notif1->id)->toBe($notif2->id);

        $count = Notification::where('idempotency_key', $idempotencyKey)->count();
        expect($count)->toBe(1);
    }

    public function test_multi_channel_in_app_delivery_record_is_created(): void
    {
        $notif = NotificationDispatcher::dispatchNotification(
            recipientId: $this->customer->id,
            type: 'booking_created',
            title: 'Test Delivery Logging',
            message: 'Testing delivery rows',
            relatedEntityId: $this->appointment->id,
            channels: ['in_app']
        );

        expect($notif)->not->toBeNull();

        $delivery = NotificationDelivery::where('notification_id', $notif->id)
            ->where('channel', 'in_app')
            ->first();

        expect($delivery)->not->toBeNull();
        expect($delivery->status)->toBe('delivered');
        expect($delivery->attempts)->toBe(1);
    }

    public function test_get_notification_preferences_returns_matrix_and_quiet_hours(): void
    {
        $response = $this->actingAs($this->customer)
            ->getJson('/api/v1/notification-preferences');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'categories',
                    'channels',
                    'preferences',
                    'quiet_hours',
                ],
            ]);
    }

    public function test_update_notification_preferences_persists_settings_and_enforces_mandatory_locks(): void
    {
        $payload = [
            'preferences' => [
                'security' => ['in_app' => false, 'push' => false, 'email' => false], // in_app & email are locked
                'booking' => ['in_app' => true, 'push' => true, 'email' => false],
                'promotion' => ['in_app' => false, 'push' => false, 'email' => false],
            ],
            'quiet_hours' => [
                'enabled' => true,
                'start' => '23:00',
                'end' => '07:00',
            ],
        ];

        $response = $this->actingAs($this->customer)
            ->putJson('/api/v1/notification-preferences', $payload);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Security in_app and email must remain true (mandatory)
        $securityInApp = UserNotificationPreference::where('user_id', $this->customer->id)
            ->where('category', 'security')
            ->where('channel', 'in_app')
            ->value('is_enabled');

        $securityEmail = UserNotificationPreference::where('user_id', $this->customer->id)
            ->where('category', 'security')
            ->where('channel', 'email')
            ->value('is_enabled');

        $bookingEmail = UserNotificationPreference::where('user_id', $this->customer->id)
            ->where('category', 'booking')
            ->where('channel', 'email')
            ->value('is_enabled');

        expect((bool) $securityInApp)->toBeTrue();
        expect((bool) $securityEmail)->toBeTrue();
        expect((bool) $bookingEmail)->toBeFalse();
    }

    public function test_register_device_token_stores_platform_and_app_version(): void
    {
        $response = $this->actingAs($this->customer)
            ->postJson('/api/v1/notifications/device-token', [
                'token' => 'ExponentPushToken[AbCdEf123456]',
                'platform' => 'ios',
                'app_version' => '2.1.0',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $token = DeviceToken::where('token', 'ExponentPushToken[AbCdEf123456]')->first();
        expect($token)->not->toBeNull();
        expect($token->platform)->toBe('ios');
        expect($token->app_version)->toBe('2.1.0');
        expect($token->is_revoked)->toBeFalse();
    }

    public function test_admin_cannot_be_accessed_by_customer(): void
    {
        $this->actingAs($this->customer)
            ->getJson('/api/v1/admin/notifications/deliveries')
            ->assertForbidden();

        $this->actingAs($this->customer)
            ->getJson('/api/v1/admin/notifications/analytics')
            ->assertForbidden();
    }

    public function test_admin_can_retrieve_deliveries_and_analytics(): void
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/notifications/deliveries')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/notifications/analytics')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'total_notifications_today',
                    'total_notifications_30d',
                    'deliveries_by_channel',
                    'active_push_tokens',
                    'active_users_with_push',
                ],
            ]);
    }

    public function test_admin_can_send_test_email_and_track_delivery(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/test-email', [
                'to' => 'tester@candycutz.com',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $delivery = NotificationDelivery::where('recipient_email', 'tester@candycutz.com')
            ->where('channel', 'email')
            ->first();

        expect($delivery)->not->toBeNull();
        expect($delivery->status)->toBe('delivered');
        expect($delivery->provider_reference)->toStartWith('resend-test-');
    }

    public function test_admin_can_dispatch_broadcast_campaign(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/notifications/broadcasts', [
                'title' => 'VIP Weekend Styling',
                'message' => 'Special discounts for all clients this weekend!',
                'audience' => 'customers',
                'channels' => ['in_app', 'push'],
                'action_url' => '/customer/dashboard',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $broadcast = Notification::where('title', 'VIP Weekend Styling')->first();
        expect($broadcast)->not->toBeNull();
        expect($broadcast->recipient_type)->toBe('all_customers');
    }

    public function test_new_domain_events_dispatch_cleanly(): void
    {
        // 1. Booking Reminder
        NotificationDispatcher::bookingReminder($this->appointment, 2);
        $reminder = Notification::where('type', 'booking_reminder')->where('recipient_id', $this->customer->id)->first();
        expect($reminder)->not->toBeNull();

        // 2. Barber Assigned
        NotificationDispatcher::barberAssigned($this->appointment, $this->barber);
        $assigned = Notification::where('type', 'barber_assigned')->where('recipient_id', $this->barberUser->id)->first();
        expect($assigned)->not->toBeNull();

        // 3. Password Changed
        NotificationDispatcher::passwordChanged($this->customer);
        $sec = Notification::where('type', 'password_changed')->where('recipient_id', $this->customer->id)->first();
        expect($sec)->not->toBeNull();
        expect($sec->priority)->toBe('urgent');

        // 4. Refund Completed
        NotificationDispatcher::refundCompleted($this->appointment, 12000.0);
        $refund = Notification::where('type', 'refund_completed')->where('recipient_id', $this->customer->id)->first();
        expect($refund)->not->toBeNull();
        expect($refund->category)->toBe('payment');
    }

    public function test_quiet_hours_suppresses_non_urgent_push_notifications(): void
    {
        DeviceToken::create([
            'user_id' => $this->customer->id,
            'token' => 'ExponentPushToken[QuietHoursDevice123]',
            'platform' => 'ios',
        ]);

        UserNotificationPreference::create([
            'user_id' => $this->customer->id,
            'category' => 'booking',
            'channel' => 'push',
            'is_enabled' => true,
            'quiet_hours_start' => '00:00',
            'quiet_hours_end' => '23:59',
        ]);

        // Non-urgent booking notification during quiet hours
        $notif = NotificationDispatcher::dispatchNotification(
            recipientId: $this->customer->id,
            type: 'booking_created',
            title: 'Quiet Hours Test',
            message: 'Testing quiet hours suppression',
            channels: ['push']
        );

        $suppressedDelivery = NotificationDelivery::where('notification_id', $notif->id)
            ->where('channel', 'push')
            ->first();

        expect($suppressedDelivery)->not->toBeNull();
        expect($suppressedDelivery->status)->toBe('suppressed');
        expect($suppressedDelivery->failure_reason)->toContain('quiet hours');
    }

    public function test_sse_stream_connects_and_accepts_cursor(): void
    {
        $response = $this->actingAs($this->customer)
            ->withHeaders(['Last-Event-ID' => '50'])
            ->get('/api/v1/notifications/stream');

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('text/event-stream');
    }
}
