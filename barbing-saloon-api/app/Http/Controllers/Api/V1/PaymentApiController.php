<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Payment\Actions\ConfirmPayment;
use App\Domain\Payment\Actions\InitiateCheckout;
use App\Domain\Payment\Actions\UploadReceipt;
use App\Domain\Payment\Actions\VerifyReceipt;
use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Payment\Services\PaymentConfirmer;
use App\Domain\Payment\Services\PricingService;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentApiController
{
    public function __construct(
        protected InitiateCheckout $initiateCheckout,
        protected ConfirmPayment $confirmPayment,
        protected UploadReceipt $uploadReceiptAction,
        protected PricingService $pricingService
    ) {}

    public function checkout(Request $request): JsonResponse
    {
        $user = $request->user();
        $appointmentId = $request->input('appointment_id');
        $paymentMethod = $request->input('payment_method', config('payments.default'));

        $appointment = Appointment::where('id', $appointmentId)->where('customer_id', $user->id)->firstOrFail();

        // Server-side amount: ignore any client-supplied amount
        $amountDue = $this->pricingService->amountDue($appointment);
        if ($amountDue <= 0 && $appointment->deposit_paid) {
            return ApiResponse::error('Appointment is already paid.', [], 409, 'PAYMENT_ALREADY_PAID');
        }

        $client = (string) $request->input('client', 'web');
        $checkoutDetails = $this->initiateCheckout->execute($user, $appointment, $paymentMethod, $client);

        return ApiResponse::success([
            'checkout' => $checkoutDetails,
            'message' => 'Payment initiated.',
        ], 'Payment initiated');
    }

    public function paymentDetails(Request $request, int $appointmentId): JsonResponse
    {
        $user = $request->user();
        $appointment = Appointment::with('barber.user')->where('id', $appointmentId)->where('customer_id', $user->id)->firstOrFail();
        $barber = $appointment->barber;

        /** @var array<int|string, array<string, string>>|null $bankAccounts */
        $bankAccounts = config('payments.gateways.manual.bank_accounts');
        $firstAccount = is_array($bankAccounts) ? collect($bankAccounts)->first() : null;

        $bankName = $firstAccount['bank_name'] ?? config('payments.gateways.manual_transfer.default_bank_name') ?? $barber?->bank_name;
        $accountName = $firstAccount['account_name'] ?? config('payments.gateways.manual_transfer.default_account_name') ?? $barber?->account_name;
        $accountNumber = $firstAccount['account_number'] ?? config('payments.gateways.manual_transfer.default_account_number') ?? $barber?->account_number;

        $isPlaceholder = in_array((string) $accountNumber, ['0123456789', '0000000000', ''], true);

        if (! $accountNumber || ! $bankName || $isPlaceholder) {
            return ApiResponse::error(
                'Payment details not configured for manual bank transfer.',
                [],
                422,
                'PAYMENT_DETAILS_NOT_CONFIGURED'
            );
        }

        return ApiResponse::success([
            'bank_name' => $bankName,
            'account_name' => $accountName ?: 'Candy Cutz Saloon',
            'account_number' => $accountNumber,
        ], 'Payment details loaded');
    }

    public function uploadReceipt(Request $request, int $appointmentId): JsonResponse
    {
        $user = $request->user();
        $appointment = Appointment::where('id', $appointmentId)->where('customer_id', $user->id)->firstOrFail();

        $request->validate([
            'receipt' => 'required|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        $file = $request->file('receipt');

        $payment = $this->uploadReceiptAction->execute($appointment, $file);

        // Dispatch notification to barber + admin
        try {
            app(NotificationDispatcher::class)->paymentReceived($appointment);
        } catch (\Throwable $e) {
            Log::warning('Could not dispatch paymentReceived notification: '.$e->getMessage());
        }

        return ApiResponse::success(
            new PaymentResource($payment),
            'Receipt uploaded successfully. Awaiting verification.'
        );
    }

    public function verifyPayment(Request $request, int $id, VerifyReceipt $verifyReceipt): JsonResponse
    {
        $appointment = Appointment::with(['payment', 'barber'])->findOrFail($id);
        $user = $request->user();

        $isAssignedBarber = $appointment->barber?->user_id === $user->id;
        $role = $user->role instanceof \BackedEnum ? $user->role->value : $user->role;
        $isStaff = in_array($role, ['admin', 'super_admin'], true);

        if (! $isAssignedBarber && ! $isStaff) {
            return ApiResponse::error('Unauthorized to verify payment for this appointment.', [], 403, 'FORBIDDEN');
        }

        $payment = $appointment->payment;
        if (! $payment) {
            return ApiResponse::error('No payment record found for this appointment.', [], 404, 'PAYMENT_NOT_FOUND');
        }

        $action = strtolower((string) $request->input('action', 'approve'));
        $approve = in_array($action, ['approve', 'approved', 'verify', 'verified'], true);
        $reason = $request->input('reason', $approve ? 'Payment verified by barber' : 'Payment rejected by barber');

        $updatedPayment = $verifyReceipt->execute($payment, $approve, $reason, $user->id);

        if ($approve) {
            if (in_array($appointment->status?->value ?? $appointment->status, [AppointmentStatus::pending->value], true)) {
                $appointment->update([
                    'status' => AppointmentStatus::confirmed->value,
                    'deposit_paid' => true,
                ]);
            }
        } else {
            // Reject handles real status update
            $previousStatus = $appointment->status?->value ?? $appointment->status;
            $appointment->update([
                'status' => AppointmentStatus::cancelled->value,
                'cancellation_reason' => $reason,
                'deposit_paid' => false,
            ]);

            try {
                AppointmentStatusHistory::create([
                    'appointment_id' => $appointment->id,
                    'previous_status' => $previousStatus,
                    'new_status' => AppointmentStatus::cancelled->value,
                    'changed_by_user_id' => $user->id,
                    'reason' => $reason,
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Could not record status history for rejected payment: '.$e->getMessage());
            }
        }

        // Audit log entry
        try {
            AuditLog::create([
                'user_id' => $user->id,
                'user_role' => $role,
                'action' => $approve ? 'payment.verified' : 'payment.rejected',
                'module' => 'Payment',
                'target_type' => Appointment::class,
                'target_id' => $appointment->id,
                'old_value' => ['deposit_paid' => $appointment->getOriginal('deposit_paid')],
                'new_value' => ['deposit_paid' => $appointment->deposit_paid, 'action' => $action, 'reason' => $reason],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Could not record audit log for payment verification: '.$e->getMessage());
        }

        // Dispatch notification to customer
        try {
            if ($approve) {
                app(NotificationDispatcher::class)->paymentVerified($appointment);
            } else {
                app(NotificationDispatcher::class)->bookingCancelled($appointment, $user);
            }
        } catch (\Throwable $e) {
            Log::warning('Could not dispatch payment verification notification: '.$e->getMessage());
        }

        return ApiResponse::success(
            new PaymentResource($updatedPayment),
            $approve ? 'Payment verified successfully.' : 'Payment rejected.'
        );
    }

    public function returnUrl(Request $request): RedirectResponse
    {
        $reference = (string) ($request->query('reference') ?? $request->query('trxref') ?? '');

        if ($reference) {
            /** @var Payment|null $payment */
            $payment = Payment::where('reference', $reference)
                ->orWhere('transaction_ref', $reference)
                ->orWhere('gateway_reference', $reference)
                ->first();

            // Accelerator: on-demand verification if still pending/initiated
            if ($payment && ! $payment->status->isSuccess()) {
                try {
                    $gateway = app(PaymentGateway::class);
                    $result = $gateway->verify($reference);
                    if ($result->isSuccessful()) {
                        app(PaymentConfirmer::class)->confirm($reference, 'return', $result->raw_payload);
                        $payment->refresh();
                    }
                } catch (\Throwable $e) {
                    Log::warning("Return URL on-demand verify failed for [{$reference}]: {$e->getMessage()}");
                }
            }

            $status = $payment?->status?->value ?? 'pending';
            $client = $payment?->client ?? $request->query('client', 'web');

            if ($client === 'mobile') {
                return redirect()->away("candycutz://payment/callback?reference={$reference}&status={$status}");
            }

            $frontendUrl = rtrim((string) (config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173'))), '/');

            return redirect()->away("{$frontendUrl}/payment/callback?reference={$reference}&status={$status}");
        }

        return redirect()->away(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')));
    }

    public function customerPaymentStatus(Request $request, string $reference): JsonResponse
    {
        $user = $request->user();
        /** @var Payment $payment */
        $payment = Payment::with(['appointment', 'customer'])
            ->where('reference', $reference)
            ->orWhere('transaction_ref', $reference)
            ->orWhere('gateway_reference', $reference)
            ->firstOrFail();

        $role = $user->role instanceof \BackedEnum ? $user->role->value : $user->role;
        $isOwner = $payment->customer_id === $user->id;
        $isStaff = in_array($role, ['admin', 'super_admin', 'barber'], true);

        if (! $isOwner && ! $isStaff) {
            return ApiResponse::error('Unauthorized.', [], 403, 'FORBIDDEN');
        }

        // On-demand verification accelerator if still pending/initiated
        if (! $payment->status->isSuccess() && ! in_array($payment->status->value, ['failed', 'rejected', 'refunded'], true)) {
            try {
                $gateway = app(PaymentGateway::class);
                $result = $gateway->verify($reference);
                if ($result->isSuccessful()) {
                    app(PaymentConfirmer::class)->confirm($reference, 'poll', $result->raw_payload);
                    $payment->refresh();
                }
            } catch (\Throwable $e) {
                Log::info("Polling verify for [{$reference}]: {$e->getMessage()}");
            }
        }

        return ApiResponse::success([
            'reference' => $payment->reference ?: $payment->transaction_ref,
            'status' => $payment->status->value,
            'amount_minor' => $payment->amount_minor ?: $payment->amount,
            'currency' => $payment->currency,
            'provider' => $payment->provider,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'appointment' => [
                'id' => $payment->appointment_id,
                'status' => $payment->appointment?->status?->value ?? $payment->appointment?->status,
                'payment_status' => $payment->appointment?->payment_status?->value ?? $payment->appointment?->payment_status,
                'deposit_paid' => (bool) $payment->appointment?->deposit_paid,
            ],
        ], 'Payment status fetched');
    }

    public function collectPaymentAtVenue(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::with(['barber', 'payment'])->findOrFail($id);
        $user = $request->user();

        $isAssignedBarber = $appointment->barber?->user_id === $user->id;
        $role = $user->role instanceof \BackedEnum ? $user->role->value : $user->role;
        $isStaff = in_array($role, ['admin', 'super_admin'], true);

        if (! $isAssignedBarber && ! $isStaff) {
            return ApiResponse::error('Unauthorized to collect payment for this appointment.', [], 403, 'FORBIDDEN');
        }

        if ($appointment->payment_status === AppointmentPaymentStatus::paid) {
            return ApiResponse::error('Appointment is already paid.', [], 409, 'PAYMENT_ALREADY_PAID');
        }

        $amountDue = $this->pricingService->amountDue($appointment);

        $payment = $appointment->payment;
        if (! $payment) {
            $payment = Payment::create([
                'appointment_id' => $appointment->id,
                'customer_id' => $appointment->customer_id ?? 1,
                'amount' => $amountDue,
                'amount_minor' => $amountDue,
                'currency' => 'NGN',
                'status' => 'pending',
                'provider' => 'venue',
                'payment_method' => 'pay_at_venue',
                'reference' => 'VENUE-'.strtoupper(Str::random(10)),
                'paid_at' => now(),
            ]);
        }

        $payment->update([
            'status' => 'successful',
            'amount_minor' => $amountDue,
            'paid_at' => now(),
            'payment_method' => 'pay_at_venue',
            'provider' => 'venue',
            'verified_at' => now(),
            'verified_by_user_id' => $user->id,
        ]);

        $appointment->update([
            'payment_status' => AppointmentPaymentStatus::paid,
            'deposit_paid' => true,
            'status' => AppointmentStatus::confirmed->value,
        ]);

        PaymentTransaction::firstOrCreate(
            ['idempotency_key' => "venue:appointment:{$appointment->id}"],
            [
                'payment_id' => $payment->id,
                'transaction_type' => 'capture',
                'gateway' => 'venue',
                'gateway_event_id' => "venue_cash_{$appointment->id}",
                'amount' => $amountDue,
                'amount_minor' => $amountDue,
                'status' => 'successful',
                'created_at' => now(),
            ]
        );

        return ApiResponse::success([
            'appointment_id' => $appointment->id,
            'payment_status' => 'paid',
            'amount_collected' => $amountDue,
        ], 'Payment collected successfully at venue.');
    }
}
