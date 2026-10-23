<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\DataObjects\GatewayResult;
use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Exceptions\PaymentException;
use App\Domain\Payment\Services\PaymentConfirmer;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class Phase2BackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_confirmer_confirms_appointment_and_is_idempotent(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $barber = Barber::factory()->create();
        $service = Service::factory()->create(['price' => 350000]);

        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'grand_total' => 350000,
            'status' => AppointmentStatus::pending->value,
            'payment_status' => AppointmentPaymentStatus::unpaid->value,
            'deposit_paid' => false,
        ]);

        $reference = 'CC-TEST-CONFIRM-1';
        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $customer->id,
            'amount' => 350000,
            'amount_minor' => 350000,
            'currency' => 'NGN',
            'status' => PaymentStatus::initiated->value,
            'provider' => 'paystack',
            'reference' => $reference,
            'transaction_ref' => $reference,
        ]);

        $confirmer = app(PaymentConfirmer::class);

        // 1. First confirmation
        $confirmed = $confirmer->confirm($reference, 'test_source', [
            'id' => 'paystack_tx_123',
            'channel' => 'card',
            'fees' => 5000,
            'gateway_response' => 'Approved',
        ]);

        $this->assertSame(PaymentStatus::successful, $confirmed->status);
        $this->assertSame(350000, $confirmed->amount_minor);
        $this->assertNotNull($confirmed->paid_at);

        $appointment->refresh();
        $this->assertSame(AppointmentPaymentStatus::paid, $appointment->payment_status);
        $this->assertTrue((bool) $appointment->deposit_paid);
        $this->assertSame(AppointmentStatus::confirmed, $appointment->status);

        $this->assertDatabaseHas('payment_transactions', [
            'payment_id' => $payment->id,
            'idempotency_key' => 'paystack:charge.success:paystack_tx_123',
            'transaction_type' => 'capture',
            'status' => 'successful',
        ]);

        // 2. Second confirmation call on same reference (idempotent duplicate call)
        $reconfirmed = $confirmer->confirm($reference, 'test_source_dup');
        $this->assertSame(PaymentStatus::successful, $reconfirmed->status);

        // Assert no duplicate transaction ledger entry was made
        $count = PaymentTransaction::where('payment_id', $payment->id)->count();
        $this->assertSame(1, $count);
    }

    public function test_payment_confirmer_rejects_and_flags_amount_underpaid(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $barber = Barber::factory()->create();
        $service = Service::factory()->create(['price' => 500000]);

        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'grand_total' => 500000,
            'status' => AppointmentStatus::pending->value,
        ]);

        $reference = 'CC-TEST-UNDERPAID-1';
        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $customer->id,
            'amount' => 100000, // Underpaid
            'amount_minor' => 100000,
            'currency' => 'NGN',
            'status' => PaymentStatus::initiated->value,
            'provider' => 'paystack',
            'reference' => $reference,
        ]);

        $confirmer = app(PaymentConfirmer::class);

        $this->expectException(PaymentException::class);

        try {
            $confirmer->confirm($reference, 'test', ['amount' => 100000]);
        } finally {
            $payment->refresh();
            $this->assertNotNull($payment->flagged_reason);
            $this->assertStringContainsString('underpaid', $payment->flagged_reason);
        }
    }

    public function test_return_url_redirects_to_web_or_mobile(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $appointment = Appointment::factory()->create(['customer_id' => $customer->id, 'grand_total' => 200000]);

        // Web payment
        $paymentWeb = Payment::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $customer->id,
            'amount' => 200000,
            'amount_minor' => 200000,
            'currency' => 'NGN',
            'status' => PaymentStatus::successful->value,
            'provider' => 'paystack',
            'reference' => 'CC-WEB-RET-1',
            'client' => 'web',
        ]);

        $respWeb = $this->get('/pay/return?reference=CC-WEB-RET-1');
        $respWeb->assertRedirect();
        $this->assertStringContainsString('/payment/callback?reference=CC-WEB-RET-1', $respWeb->headers->get('Location'));

        // Mobile payment
        $paymentMobile = Payment::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $customer->id,
            'amount' => 200000,
            'amount_minor' => 200000,
            'currency' => 'NGN',
            'status' => PaymentStatus::successful->value,
            'provider' => 'paystack',
            'reference' => 'CC-MOB-RET-1',
            'client' => 'mobile',
        ]);

        $respMobile = $this->get('/pay/return?reference=CC-MOB-RET-1');
        $respMobile->assertRedirect('candycutz://payment/callback?reference=CC-MOB-RET-1&status=successful');
    }

    public function test_customer_can_poll_payment_status(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'status' => AppointmentStatus::confirmed->value,
            'payment_status' => AppointmentPaymentStatus::paid->value,
            'deposit_paid' => true,
        ]);

        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $customer->id,
            'amount' => 300000,
            'amount_minor' => 300000,
            'currency' => 'NGN',
            'status' => PaymentStatus::successful->value,
            'provider' => 'paystack',
            'reference' => 'CC-POLL-1',
        ]);

        $response = $this->actingAs($customer)->getJson('/api/v1/customer/payments/CC-POLL-1');

        $response->assertStatus(200);
        $response->assertJsonPath('data.reference', 'CC-POLL-1');
        $response->assertJsonPath('data.status', 'successful');
        $response->assertJsonPath('data.amount_minor', 300000);
        $response->assertJsonPath('data.appointment.payment_status', 'paid');

        // Other customer cannot view
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $respForbidden = $this->actingAs($otherCustomer)->getJson('/api/v1/customer/payments/CC-POLL-1');
        $respForbidden->assertStatus(403);
    }

    public function test_barber_can_collect_payment_at_venue(): void
    {
        $userBarber = User::factory()->create(['role' => 'barber']);
        $barber = Barber::factory()->create(['user_id' => $userBarber->id]);
        $customer = User::factory()->create(['role' => 'customer']);

        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'barber_id' => $barber->id,
            'status' => AppointmentStatus::pending->value,
            'payment_status' => AppointmentPaymentStatus::unpaid->value,
            'grand_total' => 250000,
            'deposit_paid' => false,
        ]);

        $response = $this->actingAs($userBarber)->postJson("/api/v1/barber/appointments/{$appointment->id}/collect-payment");

        $response->assertStatus(200);
        $response->assertJsonPath('data.payment_status', 'paid');
        $response->assertJsonPath('data.amount_collected', 250000);

        $appointment->refresh();
        $this->assertSame(AppointmentPaymentStatus::paid, $appointment->payment_status);
        $this->assertTrue((bool) $appointment->deposit_paid);

        $this->assertDatabaseHas('payments', [
            'appointment_id' => $appointment->id,
            'payment_method' => 'pay_at_venue',
            'provider' => 'venue',
            'status' => 'successful',
        ]);
    }

    public function test_admin_payment_management_and_refund(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $appointment = Appointment::factory()->create(['customer_id' => $customer->id, 'grand_total' => 400000]);

        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $customer->id,
            'amount' => 400000,
            'amount_minor' => 400000,
            'currency' => 'NGN',
            'status' => PaymentStatus::successful->value,
            'provider' => 'paystack',
            'reference' => 'CC-ADMIN-TEST-1',
        ]);

        // 1. List payments
        $listResp = $this->actingAs($admin)->getJson('/api/v1/admin/payments');
        $listResp->assertStatus(200);
        $listResp->assertJsonPath('data.total', 1);

        // 2. Refund payment
        $refundResp = $this->actingAs($admin)->postJson("/api/v1/admin/payments/{$payment->id}/refund", [
            'amount_minor' => 400000,
            'reason' => 'Customer was dissatisfied',
        ]);

        $refundResp->assertStatus(200);
        $refundResp->assertJsonPath('data.status', 'refunded');

        $payment->refresh();
        $this->assertSame(PaymentStatus::refunded, $payment->status);
        $this->assertSame(400000, $payment->refunded_minor);

        $this->assertDatabaseHas('payment_transactions', [
            'payment_id' => $payment->id,
            'transaction_type' => 'refund',
        ]);
    }

    public function test_paystack_refund_calls_provider_before_updating_local_payment(): void
    {
        Config::set('payments.default', 'paystack');
        Config::set('services.paystack.secret_key', 'sk_test_refund_key');
        Http::fake([
            'https://api.paystack.co/refund' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 12345,
                    'status' => 'processed',
                    'amount' => 100000,
                    'currency' => 'NGN',
                    'transaction' => ['reference' => 'CC-REFUND-1'],
                ],
            ], 200),
        ]);

        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $appointment = Appointment::factory()->create(['customer_id' => $customer->id]);
        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $customer->id,
            'amount' => 100000,
            'amount_minor' => 100000,
            'currency' => 'NGN',
            'status' => PaymentStatus::successful->value,
            'provider' => 'paystack',
            'reference' => 'CC-REFUND-1',
        ]);

        $this->actingAs($admin)->postJson("/api/v1/admin/payments/{$payment->id}/refund", [
            'amount_minor' => 100000,
            'reason' => 'Customer requested a refund',
        ])->assertOk();

        Http::assertSent(fn ($request) => $request->url() === 'https://api.paystack.co/refund'
            && $request['transaction'] === 'CC-REFUND-1'
            && $request['amount'] === 100000);
        $this->assertSame(PaymentStatus::refunded, $payment->fresh()->status);
    }

    public function test_failed_paystack_refund_does_not_change_local_payment_state(): void
    {
        Config::set('payments.default', 'paystack');
        Config::set('services.paystack.secret_key', 'sk_test_refund_key');
        Http::fake([
            'https://api.paystack.co/refund' => Http::response([
                'status' => false,
                'message' => 'Refund rejected',
            ], 422),
        ]);

        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $appointment = Appointment::factory()->create(['customer_id' => $customer->id]);
        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $customer->id,
            'amount' => 100000,
            'amount_minor' => 100000,
            'currency' => 'NGN',
            'status' => PaymentStatus::successful->value,
            'provider' => 'paystack',
            'reference' => 'CC-REFUND-FAIL-1',
        ]);

        $this->actingAs($admin)->postJson("/api/v1/admin/payments/{$payment->id}/refund", [
            'amount_minor' => 100000,
            'reason' => 'Customer requested a refund',
        ])->assertStatus(502);

        $this->assertSame(PaymentStatus::successful, $payment->fresh()->status);
        $this->assertSame(0, $payment->fresh()->refunded_minor);
        $this->assertDatabaseMissing('payment_transactions', [
            'payment_id' => $payment->id,
            'transaction_type' => 'refund',
            'status' => 'successful',
        ]);
    }

    public function test_reconciliation_command_handles_stale_payments(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $appointment = Appointment::factory()->create(['customer_id' => $customer->id, 'grand_total' => 150000]);

        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $customer->id,
            'amount' => 150000,
            'amount_minor' => 150000,
            'currency' => 'NGN',
            'status' => PaymentStatus::pending->value,
            'provider' => 'paystack',
            'reference' => 'CC-RECON-1',
        ]);

        Payment::where('id', $payment->id)->update(['created_at' => now()->subMinutes(15)]);

        // Mock gateway verify
        $gatewayMock = Mockery::mock(PaymentGateway::class);
        $gatewayMock->shouldReceive('verify')
            ->with('CC-RECON-1')
            ->once()
            ->andReturn(new GatewayResult(
                reference: 'CC-RECON-1',
                status: 'success',
                amount_kobo: 150000,
                gateway: 'paystack',
                gateway_event_id: 'rec_tx_999',
                raw_payload: ['id' => 'rec_tx_999', 'status' => 'success']
            ));

        $this->app->instance(PaymentGateway::class, $gatewayMock);

        $exit = Artisan::call('payments:reconcile', ['--minutes' => 10]);
        $this->assertSame(0, $exit);

        $payment->refresh();
        $this->assertSame(PaymentStatus::successful, $payment->status);
    }
}
