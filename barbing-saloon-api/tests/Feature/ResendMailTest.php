<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Identity\Actions\SocialLogin;
use App\Domain\Identity\Services\ApiTokenIssuer;
use App\Domain\Identity\Services\UsernameIdentityService;
use App\Domain\Notification\Channels\EmailNotificationChannel;
use App\Jobs\SendAdminNotification;
use App\Jobs\SendBookingCancellation;
use App\Jobs\SendBookingConfirmation;
use App\Jobs\SendBookingReminder;
use App\Jobs\SendBookingThankYou;
use App\Jobs\SendNotificationEmail;
use App\Mail\AdminNewUserNotification;
use App\Mail\AdminNotification;
use App\Mail\BookingCancellation;
use App\Mail\BookingConfirmation;
use App\Mail\BookingReminder;
use App\Mail\BookingThankYou;
use App\Mail\WelcomeCustomer;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ResendMailTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $admin;
    private Barber $barber;
    private Service $service;
    private Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('forgot-password:*');
        RateLimiter::clear('admin-test-email:*');

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'John Customer',
            'email' => 'customer@candycutz.test',
        ]);

        $barberUser = User::factory()->create([
            'role' => 'barber',
            'name' => 'Sam Barber',
            'email' => 'sam@candycutz.test',
        ]);

        $this->barber = Barber::create([
            'user_id' => $barberUser->id,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Super Admin',
            'email' => 'admin@candycutz.test',
        ]);

        $this->service = Service::factory()->create([
            'name' => 'Signature Beard Trim',
            'price' => 5000,
            'duration_minutes' => 30,
        ]);

        $this->appointment = Appointment::create([
            'booking_reference' => 'BK-TEST-1234',
            'branch_id' => 1,
            'customer_id' => $this->customer->id,
            'barber_id' => $this->barber->id,
            'service_id' => $this->service->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '14:00',
            'end_time' => '14:30',
            'status' => 'confirmed',
            'total_price' => 5000,
            'total_amount' => 5000,
            'grand_total' => 5000,
            'deposit_paid' => false,
            'client_name' => $this->customer->name,
            'client_phone' => '+2348000000000',
            'client_email' => $this->customer->email,
            'source' => 'web',
        ]);
    }

    public function test_password_reset_email_dispatches_cleanly_and_stores_token(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $this->customer->email,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $this->customer->email,
        ]);
    }

    public function test_password_reset_is_rate_limited_after_three_attempts(): void
    {
        Mail::fake();

        // 3 allowed requests
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/v1/auth/forgot-password', [
                'email' => $this->customer->email,
            ]);
            $response->assertOk();
        }

        // 4th request must be throttled
        $throttled = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $this->customer->email,
        ]);

        $throttled->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'TOO_MANY_REQUESTS');
    }

    public function test_booking_confirmation_email_job_queues_mailable(): void
    {
        Mail::fake();

        $job = new SendBookingConfirmation($this->appointment);
        $job->handle();

        Mail::assertQueued(BookingConfirmation::class, function (BookingConfirmation $mail) {
            return $mail->hasTo($this->customer->email)
                && $mail->hasSubject('Your CandyCutz Booking is Confirmed');
        });
    }

    public function test_booking_cancellation_email_job_queues_mailable(): void
    {
        Mail::fake();

        $job = new SendBookingCancellation($this->appointment);
        $job->handle();

        Mail::assertQueued(BookingCancellation::class, function (BookingCancellation $mail) {
            return $mail->hasTo($this->customer->email)
                && $mail->hasSubject('Your CandyCutz Booking Has Been Cancelled');
        });
    }

    public function test_booking_reminder_email_job_queues_mailable(): void
    {
        Mail::fake();

        $job = new SendBookingReminder($this->appointment);
        $job->handle();

        Mail::assertQueued(BookingReminder::class, function (BookingReminder $mail) {
            return $mail->hasTo($this->customer->email)
                && $mail->hasSubject('Reminder: Your CandyCutz Appointment is Tomorrow');
        });
    }

    public function test_booking_thank_you_email_job_queues_mailable(): void
    {
        Mail::fake();

        $job = new SendBookingThankYou($this->appointment);
        $job->handle();

        Mail::assertQueued(BookingThankYou::class, function (BookingThankYou $mail) {
            return $mail->hasTo($this->customer->email)
                && $mail->hasSubject('Thank You for Visiting CandyCutz');
        });
    }

    public function test_admin_notification_email_job_queues_mailable(): void
    {
        Mail::fake();

        $job = new SendAdminNotification($this->appointment, 'new_booking');
        $job->handle();

        Mail::assertQueued(AdminNotification::class, function (AdminNotification $mail) {
            return $mail->hasTo($this->admin->email)
                && $mail->hasSubject('CandyCutz Admin Alert: New Booking');
        });
    }

    public function test_admin_can_send_test_email_via_mail_facade_and_record_delivery(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/test-email', [
                'to' => 'test-recipient@candycutz.test',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sent_to', 'test-recipient@candycutz.test');

        $delivery = NotificationDelivery::where('recipient_email', 'test-recipient@candycutz.test')->first();
        expect($delivery)->not->toBeNull();
        expect($delivery->status)->toBe('delivered');
        expect($delivery->provider_reference)->toStartWith('resend-test-');
    }

    public function test_admin_test_email_renders_luxury_template_with_preheader_and_diagnostics(): void
    {
        $html = view('mail.connection-test', [
            'recipient' => 'test-recipient@candycutz.test',
            'fromAddress' => 'onboarding@resend.dev',
            'fromName' => 'CandyCutz Luxury Grooming',
            'dispatchedAt' => 'Nov 22, 2026 • 6:40 AM CET',
            'environment' => 'testing',
            'adminUrl' => 'http://localhost:5173/admin/integrations/resend',
        ])->render();

        expect($html)->toContain('CANDYCUTZ');
        expect($html)->toContain('Luxury Grooming &amp; Barbing Saloon');
        expect($html)->toContain('Resend Connected &amp; Verified');
        expect($html)->toContain('test-recipient@candycutz.test');
        expect($html)->toContain('onboarding@resend.dev');
        expect($html)->toContain('Keffi');
    }

    public function test_admin_test_email_is_rate_limited(): void
    {
        Mail::fake();

        // 5 requests allowed
        for ($i = 0; $i < 5; $i++) {
            $response = $this->actingAs($this->admin)
                ->postJson('/api/v1/admin/test-email', [
                    'to' => "recipient{$i}@candycutz.test",
                ]);
            $response->assertOk();
        }

        // 6th request throttled
        $throttled = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/test-email', [
                'to' => 'excessive@candycutz.test',
            ]);

        $throttled->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'TOO_MANY_REQUESTS');
    }

    public function test_notification_email_job_executes_and_marks_delivery_as_delivered(): void
    {
        Mail::fake();

        $notification = Notification::create([
            'recipient_id' => $this->customer->id,
            'recipient_type' => 'user',
            'type' => 'booking_confirmed',
            'category' => 'booking',
            'title' => 'Booking Confirmed',
            'message' => 'Your grooming appointment is confirmed.',
            'data' => [],
        ]);

        $delivery = NotificationDelivery::create([
            'notification_id' => $notification->id,
            'channel' => 'email',
            'recipient_email' => $this->customer->email,
            'status' => 'pending',
            'attempts' => 0,
        ]);

        $job = new SendNotificationEmail(
            $delivery->id,
            $this->customer->email,
            'CandyCutz — Your Appointment',
            '<p>Appointment details here</p>'
        );
        $job->handle();

        $delivery->refresh();
        expect($delivery->status)->toBe('delivered');
        expect($delivery->provider_reference)->toStartWith('resend-');
    }

    public function test_email_channel_suppresses_rapid_duplicate_dispatches(): void
    {
        Queue::fake();

        $notification = Notification::create([
            'recipient_id' => $this->customer->id,
            'recipient_type' => 'user',
            'type' => 'booking_confirmed',
            'category' => 'booking',
            'title' => 'Booking Confirmed',
            'message' => 'Your appointment is confirmed.',
            'data' => [],
        ]);

        $channel = app(EmailNotificationChannel::class);

        // First dispatch should queue job
        $channel->send($this->customer, $notification);
        Queue::assertPushed(SendNotificationEmail::class, 1);

        // Immediate duplicate dispatch within rate limit window is suppressed
        $channel->send($this->customer, $notification);
        Queue::assertPushed(SendNotificationEmail::class, 1);
    }

    public function test_new_google_registration_dispatches_welcome_email_and_alerts_admin(): void
    {
        Mail::fake();

        $action = new class(app(UsernameIdentityService::class), app(ApiTokenIssuer::class)) extends SocialLogin {
            protected function verifyAndDecodeIdToken(string $provider, string $idToken): array
            {
                return [
                    'sub' => 'google-new-user-12345',
                    'email' => 'new-google-user@candycutz.test',
                    'email_verified' => true,
                    'name' => 'Michael VIP',
                    'picture' => 'https://example.com/avatar.jpg',
                ];
            }
        };

        $result = $action->execute([
            'provider' => 'google',
            'id_token' => 'mock-valid-google-id-token',
        ]);

        $createdUser = $result['user'];
        expect($createdUser->email)->toBe('new-google-user@candycutz.test');

        // Assert customer welcome email queued
        Mail::assertQueued(WelcomeCustomer::class, function (WelcomeCustomer $mail) use ($createdUser) {
            return $mail->hasTo($createdUser->email)
                && $mail->hasSubject('Welcome to CandyCutz Luxury Grooming 💈');
        });

        // Assert admin alert email queued
        Mail::assertQueued(AdminNewUserNotification::class, function (AdminNewUserNotification $mail) use ($createdUser) {
            return $mail->hasTo($this->admin->email)
                && str_contains($mail->envelope()->subject, 'CandyCutz Admin Alert: New User Registration');
        });

        // Assert in-app notifications persisted
        expect(Notification::where('type', 'account_created')->where('recipient_id', $createdUser->id)->exists())->toBeTrue();
        expect(Notification::where('type', 'user_registered_admin')->where('recipient_type', 'all_admins')->exists())->toBeTrue();
    }

    public function test_welcome_and_admin_new_user_templates_render_cleanly(): void
    {
        $welcomeHtml = view('mail.welcome-customer', [
            'user' => $this->customer,
            'userName' => 'John Customer',
            'dashboardUrl' => 'http://localhost:5173/customer/dashboard',
            'servicesUrl' => 'http://localhost:5173/services',
        ])->render();

        expect($welcomeHtml)->toContain('Welcome to CandyCutz, John Customer');
        expect($welcomeHtml)->toContain('Master Stylists');
        expect($welcomeHtml)->toContain('Keffi');

        $adminHtml = view('mail.admin-new-user', [
            'user' => $this->customer,
            'provider' => 'google',
            'providerLabel' => 'Google OAuth',
            'registeredAt' => 'Nov 22, 2026 • 7:15 AM CET',
            'adminUrl' => 'http://localhost:5173/admin/customers',
        ])->render();

        expect($adminHtml)->toContain('New Customer Joined CandyCutz');
        expect($adminHtml)->toContain('Google OAuth');
        expect($adminHtml)->toContain($this->customer->email);
    }
}
