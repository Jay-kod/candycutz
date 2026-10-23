<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Exceptions\PaymentException;
use App\Domain\Payment\Services\PaymentConfirmer;
use App\Domain\Payment\Services\PaymentStateMachine;
use App\Http\Responses\ApiResponse;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\WebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminPaymentApiController
{
    public function __construct(
        protected PaymentConfirmer $paymentConfirmer,
        protected PaymentStateMachine $stateMachine,
        protected PaymentGateway $gateway
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['appointment.customer', 'customer'])
            ->orderBy('created_at', 'desc');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($provider = $request->input('provider')) {
            $query->where('provider', $provider);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('transaction_ref', 'like', "%{$search}%")
                    ->orWhere('gateway_reference', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $payments = $query->paginate($perPage);

        return ApiResponse::success($payments, 'Payments retrieved successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $payment = Payment::with(['appointment.customer', 'customer', 'transactions'])->findOrFail($id);

        return ApiResponse::success($payment, 'Payment details retrieved.');
    }

    public function reverify(Request $request, int $id): JsonResponse
    {
        $payment = Payment::with('appointment')->findOrFail($id);
        $reference = $payment->reference ?: $payment->transaction_ref ?: $payment->gateway_reference;

        if (! $reference) {
            return ApiResponse::error('Payment has no valid reference to re-verify.', [], 422, 'PAYMENT_NO_REFERENCE');
        }

        try {
            $result = $this->gateway->verify($reference);
            if ($result->isSuccessful()) {
                $this->paymentConfirmer->confirm($reference, 'admin_reverify', $result->raw_payload);
                $payment->refresh();
            } else {
                $payment->update([
                    'status' => PaymentStatus::failed->value,
                    'gateway_response' => $result->raw_payload['gateway_response'] ?? 'Verification failed',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("Admin reverify error for payment [{$id}]: {$e->getMessage()}");

            return ApiResponse::error('Gateway verification failed: '.$e->getMessage(), [], 502, 'GATEWAY_ERROR');
        }

        return ApiResponse::success($payment->fresh(), 'Payment re-verified successfully.');
    }

    public function refund(Request $request, int $id): JsonResponse
    {
        $payment = Payment::with('appointment')->findOrFail($id);
        $user = $request->user();

        if ($payment->provider === 'manual_transfer') {
            return ApiResponse::error('Manual transfer refunds must be processed outside the payment gateway.', [], 422, 'PAYMENT_NOT_REFUNDABLE');
        }

        if (! $payment->status->isSuccess()) {
            return ApiResponse::error('Only successful payments can be refunded.', [], 422, 'PAYMENT_NOT_REFUNDABLE');
        }

        $refundAmount = (int) $request->input('amount_minor', $payment->amount_minor ?: $payment->amount);
        $reason = (string) $request->input('reason', 'Customer refund requested by admin');

        try {
            $refundResult = $this->gateway->refund($payment, $refundAmount, $reason);
            if (! $refundResult->isSuccessful()) {
                throw PaymentException::providerRejected(
                    $refundResult->raw_payload['message'] ?? 'Refund rejected by provider.'
                );
            }
        } catch (\Throwable $e) {
            Log::error('Refund failed for payment ['.$payment->id.']: '.$e->getMessage());

            return ApiResponse::error(
                $e->getMessage(),
                ['provider' => $payment->provider, 'amount_minor' => $refundAmount],
                502,
                'PAYMENT_PROVIDER_REJECTED'
            );
        }

        $isFull = $refundAmount >= ($payment->amount_minor ?: $payment->amount);
        $targetStatus = $isFull ? PaymentStatus::refunded : PaymentStatus::partially_refunded;

        $this->stateMachine->transition($payment, $targetStatus, [
            'refunded_minor' => ($payment->refunded_minor ?? 0) + $refundAmount,
            'gateway_response' => $refundResult->raw_payload,
        ]);

        PaymentTransaction::create([
            'payment_id' => $payment->id,
            'transaction_type' => 'refund',
            'gateway' => $payment->provider ?: 'paystack',
            'gateway_event_id' => $refundResult->gateway_event_id ?: 'admin_refund_'.uniqid(),
            'amount' => $refundAmount,
            'amount_minor' => $refundAmount,
            'raw_payload' => ['reason' => $reason, 'refunded_by' => $user->id, 'provider_response' => $refundResult->raw_payload],
            'status' => 'successful',
            'created_at' => now(),
        ]);

        try {
            AuditLog::create([
                'user_id' => $user->id,
                'user_role' => $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role,
                'action' => 'payment.refunded',
                'module' => 'Payment',
                'target_type' => Payment::class,
                'target_id' => $payment->id,
                'new_value' => [
                    'refund_amount' => $refundAmount,
                    'reason' => $reason,
                    'status' => $targetStatus->value,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Could not write audit log for refund: '.$e->getMessage());
        }

        return ApiResponse::success($payment->fresh(), 'Payment refund processed.');
    }

    public function webhookEvents(Request $request): JsonResponse
    {
        $query = WebhookEvent::orderBy('received_at', 'desc');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $perPage = min((int) $request->input('per_page', 25), 100);
        $events = $query->paginate($perPage);

        return ApiResponse::success($events, 'Webhook events retrieved.');
    }

    public function replayWebhookEvent(Request $request, int $id): JsonResponse
    {
        $event = WebhookEvent::findOrFail($id);

        try {
            $payload = $event->payload;
            $data = $payload['data'] ?? $payload;
            $reference = (string) ($data['reference'] ?? '');

            if (! $reference) {
                return ApiResponse::error('Webhook payload has no reference to replay.', [], 422, 'INVALID_PAYLOAD');
            }

            if ($event->event === 'charge.success' || ($data['status'] ?? '') === 'success') {
                $this->paymentConfirmer->confirm($reference, 'admin_replay', $data);
            }

            $event->markProcessed();
        } catch (\Throwable $e) {
            $event->markFailed($e->getMessage());

            return ApiResponse::error('Webhook replay failed: '.$e->getMessage(), [], 500, 'REPLAY_FAILED');
        }

        return ApiResponse::success($event->fresh(), 'Webhook event replayed successfully.');
    }
}
