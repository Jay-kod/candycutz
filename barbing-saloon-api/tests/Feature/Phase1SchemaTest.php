<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase1SchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_payments_table_has_paystack_columns(): void
    {
        $expected = [
            'id', 'appointment_id', 'customer_id',
            'provider', 'reference', 'access_code', 'authorization_url',
            'amount', 'amount_minor', 'currency', 'status',
            'provider_transaction_id', 'channel', 'gateway_response',
            'fees_minor', 'paid_at', 'refunded_minor',
            'failure_reason', 'client', 'customer_email',
            'expires_at', 'flagged_reason',
        ];

        foreach ($expected as $col) {
            $this->assertTrue(
                Schema::hasColumn('payments', $col),
                "Missing column: payments.{$col}"
            );
        }
    }

    public function test_payment_transactions_has_new_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'amount_minor'));
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'idempotency_key'));
    }

    public function test_webhook_events_table_exists_with_columns(): void
    {
        $this->assertTrue(Schema::hasTable('webhook_events'));

        $expected = [
            'id', 'provider', 'event', 'provider_event_key',
            'signature_valid', 'payload', 'status', 'attempts',
            'error', 'request_id', 'received_at', 'processed_at',
        ];

        foreach ($expected as $col) {
            $this->assertTrue(
                Schema::hasColumn('webhook_events', $col),
                "Missing column: webhook_events.{$col}"
            );
        }
    }

    public function test_appointments_table_has_payment_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('appointments', 'payment_status'));
        $this->assertTrue(Schema::hasColumn('appointments', 'amount_due_minor'));
        $this->assertTrue(Schema::hasColumn('appointments', 'payment_due_at'));
    }

    public function test_payment_model_casts_status_enum(): void
    {
        $payment = new Payment;
        $payment->status = 'initiated';

        $this->assertInstanceOf(PaymentStatus::class, $payment->status);
        $this->assertSame(PaymentStatus::initiated, $payment->status);
    }

    public function test_appointment_model_casts_payment_status_enum(): void
    {
        $appointment = new Appointment;
        $appointment->payment_status = 'unpaid';

        $this->assertInstanceOf(AppointmentPaymentStatus::class, $appointment->payment_status);
        $this->assertSame(AppointmentPaymentStatus::unpaid, $appointment->payment_status);
    }

    public function test_webhook_event_model_can_be_created(): void
    {
        $event = WebhookEvent::create([
            'provider' => 'paystack',
            'event' => 'charge.success',
            'provider_event_key' => 'paystack:charge.success:test-123',
            'signature_valid' => true,
            'payload' => ['data' => ['reference' => 'test-123']],
            'status' => 'received',
            'received_at' => now(),
        ]);

        $this->assertDatabaseHas('webhook_events', [
            'id' => $event->id,
            'provider' => 'paystack',
            'event' => 'charge.success',
            'status' => 'received',
        ]);

        $event->markProcessed();
        $event->refresh();

        $this->assertSame('processed', $event->status);
        $this->assertNotNull($event->processed_at);
    }

    public function test_payment_transaction_model_has_idempotency_key(): void
    {
        $tx = new PaymentTransaction;
        $tx->idempotency_key = 'paystack:charge.success:ref-abc';

        $this->assertSame('paystack:charge.success:ref-abc', $tx->idempotency_key);
    }
}
