<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Payment\Actions\ConfirmPayment;
use App\Domain\Payment\Actions\InitiateCheckout;
use App\Domain\Payment\Actions\UploadReceipt;
use App\Domain\Payment\Actions\VerifyReceipt;
use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Services\PaymentConfirmer;
use App\Domain\Payment\Services\PaymentService;
use App\Domain\Payment\Services\PricingService;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Events\PaymentConfirmed;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\AuditLog;
use App\Models\Barber;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $dbBankName = Setting::query()->where('key', 'bank_name')->value('value')
            ?: Setting::query()->where('key', 'manual_transfer_bank')->value('value');
        $dbAccountName = Setting::query()->where('key', 'account_name')->value('value')
            ?: Setting::query()->where('key', 'manual_transfer_account_name')->value('value');
        $dbAccountNumber = Setting::query()->where('key', 'account_number')->value('value')
            ?: Setting::query()->where('key', 'manual_transfer_account_number')->value('value');

        $bankName = $barber?->bank_name
            ?: ($dbBankName
            ?: ($firstAccount['bank_name']
            ?? config('payments.gateways.manual_transfer.default_bank_name')));

        $accountName = $barber?->account_name
            ?: ($dbAccountName
            ?: ($firstAccount['account_name']
            ?? config('payments.gateways.manual_transfer.default_account_name')));

        $accountNumber = $barber?->account_number
            ?: ($dbAccountNumber
            ?: ($firstAccount['account_number']
            ?? config('payments.gateways.manual_transfer.default_account_number')));

        $isPlaceholder = in_array((string) $accountNumber, ['0000000000', ''], true);

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
            if (in_array($appointment->status->value ?? $appointment->status, [AppointmentStatus::pending->value], true)) {
                $appointment->update([
                    'status' => AppointmentStatus::confirmed->value,
                    'payment_status' => AppointmentPaymentStatus::paid,
                    'deposit_paid' => true,
                ]);
            }
        } else {
            // Reject handles real status update
            $previousStatus = $appointment->status->value ?? $appointment->status;
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

        // Dispatch cancellation notification when rejected (approvals dispatch PaymentConfirmed in state machine)
        if (! $approve) {
            try {
                app(NotificationDispatcher::class)->bookingCancelled($appointment, $user);
            } catch (\Throwable $e) {
                Log::warning('Could not dispatch payment verification notification: '.$e->getMessage());
            }
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
            $payment = Payment::where(function ($q) use ($reference) {
                $q->where('reference', $reference)
                    ->orWhere('transaction_ref', $reference)
                    ->orWhere('gateway_reference', $reference);
            })->first();

            // Accelerator: on-demand verification if still pending/initiated
            if ($payment && ! $payment->status->isSuccess()) {
                try {
                    $paymentService = app(PaymentService::class);
                    $gateway = $paymentService->resolveGateway($payment->provider ?: $payment->payment_method ?: 'paystack');
                    $result = $gateway->verify($reference);
                    if ($result->isSuccessful()) {
                        app(PaymentConfirmer::class)->confirm($reference, 'return', $result->raw_payload);
                        $payment->refresh();
                    }
                } catch (\Throwable $e) {
                    Log::warning("Return URL on-demand verify failed for [{$reference}]: {$e->getMessage()}");
                }
            }

            $status = $payment?->status->value ?? 'pending';
            $client = $payment->client ?? $request->query('client', 'web');

            if ($client === 'mobile') {
                return redirect()->away("candycutz://payment/callback?reference={$reference}&status={$status}");
            }

            $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');

            return redirect()->away("{$frontendUrl}/customer/dashboard/payment/callback?reference={$reference}&status={$status}");
        }

        return redirect()->away(config('app.frontend_url') ?: 'http://localhost:5173');
    }

    public function customerPaymentStatus(Request $request, string $reference): JsonResponse
    {
        $user = $request->user();
        /** @var Payment $payment */
        $payment = Payment::with(['appointment.service', 'appointment.barber.user', 'appointment.customer', 'customer'])
            ->where(function ($q) use ($reference) {
                $q->where('reference', $reference)
                    ->orWhere('transaction_ref', $reference)
                    ->orWhere('gateway_reference', $reference);
            })
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
                $paymentService = app(PaymentService::class);
                $gateway = $paymentService->resolveGateway($payment->provider ?: $payment->payment_method ?: 'paystack');
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
            'payment_method' => $payment->channel ?: $payment->payment_method ?: $payment->provider,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'appointment' => $payment->appointment ? [
                'id' => $payment->appointment->id,
                'booking_reference' => $payment->appointment->booking_reference,
                'verification_code' => $payment->appointment->verification_code,
                'status' => $payment->appointment->status instanceof \BackedEnum ? $payment->appointment->status->value : (string) $payment->appointment->status,
                'payment_status' => $payment->appointment->payment_status instanceof \BackedEnum ? $payment->appointment->payment_status->value : (string) $payment->appointment->payment_status,
                'deposit_paid' => (bool) $payment->appointment->deposit_paid,
                'appointment_date' => $payment->appointment->appointment_date ? (is_string($payment->appointment->appointment_date) ? $payment->appointment->appointment_date : $payment->appointment->appointment_date->format('Y-m-d')) : null,
                'appointment_time' => $payment->appointment->appointment_time,
                'total_duration_minutes' => (int) ($payment->appointment->total_duration_minutes ?: $payment->appointment->service?->duration_minutes ?: 30),
                'total_price' => (float) ($payment->appointment->grand_total ?: $payment->appointment->total_price ?: ($payment->amount_minor ? $payment->amount_minor / 100 : $payment->amount)),
                'service' => $payment->appointment->service ? [
                    'id' => $payment->appointment->service->id,
                    'name' => $payment->appointment->service->name,
                    'price' => (float) $payment->appointment->service->price,
                    'duration_minutes' => (int) ($payment->appointment->service->duration_minutes ?? 30),
                ] : null,
                'barber' => $payment->appointment->barber ? [
                    'id' => $payment->appointment->barber->id,
                    'name' => $payment->appointment->barber->user->name ?? 'Master Barber',
                    'chair_number' => 1,
                ] : null,
            ] : null,
        ], 'Payment status fetched');
    }

    public function collectPaymentAtVenue(Request $request, int $id): JsonResponse
    {
        $preCheck = Appointment::with(['barber'])->findOrFail($id);
        $user = $request->user();

        $isAssignedBarber = $preCheck->barber?->user_id === $user->id;
        $role = $user->role instanceof \BackedEnum ? $user->role->value : $user->role;
        $isStaff = in_array($role, ['admin', 'super_admin'], true);

        if (! $isAssignedBarber && ! $isStaff) {
            return ApiResponse::error('Unauthorized to collect payment for this appointment.', [], 403, 'FORBIDDEN');
        }

        /** @var array{already_paid: bool, payment?: Payment, appointment?: Appointment, amount_due?: int} $txResult */
        $txResult = DB::transaction(function () use ($id, $user) {
            $appointment = Appointment::with(['barber', 'payment'])->lockForUpdate()->findOrFail($id);

            if ($appointment->payment_status === AppointmentPaymentStatus::paid) {
                return ['already_paid' => true];
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

            return [
                'already_paid' => false,
                'payment' => $payment->fresh(),
                'appointment' => $appointment->fresh(),
                'amount_due' => $amountDue,
            ];
        }, 3);

        if ($txResult['already_paid']) {
            return ApiResponse::error('Appointment is already paid.', [], 409, 'PAYMENT_ALREADY_PAID');
        }

        try {
            PaymentConfirmed::dispatch($txResult['payment'], $txResult['appointment']);
        } catch (\Throwable $e) {
            Log::warning('Could not dispatch PaymentConfirmed for venue collection: '.$e->getMessage());
        }

        return ApiResponse::success([
            'appointment_id' => $txResult['appointment']->id,
            'payment_status' => 'paid',
            'amount_collected' => $txResult['amount_due'],
        ], 'Payment collected successfully at venue.');
    }

    public function checkoutData(Request $request): JsonResponse
    {
        $appointmentId = $request->query('appointment_id');
        $reference = $request->query('reference');

        $appointment = null;
        if ($appointmentId) {
            $appointment = Appointment::with(['barber.user', 'service', 'customer'])->find($appointmentId);
        } elseif ($reference) {
            $appointment = Appointment::with(['barber.user', 'service', 'customer'])
                ->where('booking_reference', $reference)
                ->first();
        }

        // Central Salon Bank & Checkout Settings from DB
        $dbBankName = Setting::query()->where('key', 'bank_name')->value('value')
            ?: Setting::query()->where('key', 'manual_transfer_bank')->value('value')
            ?: config('payments.gateways.manual.default_bank_name')
            ?: 'Guaranty Trust Bank (GTB)';
        $dbAccountName = Setting::query()->where('key', 'account_name')->value('value')
            ?: Setting::query()->where('key', 'manual_transfer_account_name')->value('value')
            ?: 'Candy Cutz Saloon';
        $dbAccountNumber = Setting::query()->where('key', 'account_number')->value('value')
            ?: Setting::query()->where('key', 'manual_transfer_account_number')->value('value')
            ?: '0123456789';

        $gatewayTitle = Setting::query()->where('key', 'payment_gateway_title')->value('value')
            ?: 'CandyCutz • Secure Payment';
        $expiryMinutes = (int) (Setting::query()->where('key', 'payment_expiry_minutes')->value('value') ?: 120);
        $noticeText = Setting::query()->where('key', 'payment_notice_text')->value('value')
            ?: 'Before transferring, make sure the account name shown by your bank matches the one provided below.';
        $rawChannels = Setting::query()->where('key', 'payment_channels')->value('value');
        $channels = $rawChannels ? json_decode($rawChannels, true) : ['bank_transfer', 'ussd', 'bank_account', 'nqr', 'card'];
        $ussdTemplate = Setting::query()->where('key', 'ussd_code_template')->value('value')
            ?: '*737*2*{amount}*{account_number}#';
        $autoConfirm = filter_var(Setting::query()->where('key', 'payment_auto_confirm')->value('value') ?? true, FILTER_VALIDATE_BOOLEAN);

        $barbers = Barber::with('user')->get()->map(function ($b) {
            /** @var Barber $b */
            $u = $b->user;

            return [
                'id' => $b->id,
                'name' => $u ? $u->name : 'Master Barber',
                'avatar' => $u ? $u->avatar : null,
                'specialty' => is_array($b->specialties) ? implode(', ', $b->specialties) : ($b->specialties ?? 'Master Stylist'),
                'chair_number' => 1,
                'bank_name' => $b->bank_name,
                'account_name' => $b->account_name,
                'account_number' => $b->account_number,
            ];
        });

        $services = Service::where('is_active', true)->get()->map(function ($s) {
            /** @var Service $s */
            return [
                'id' => $s->id,
                'name' => $s->name,
                'price' => $s->price,
                'duration_minutes' => $s->duration_minutes,
            ];
        });

        $appointmentData = null;
        if ($appointment) {
            $b = $appointment->barber;
            $bu = $b ? $b->user : null;
            $s = $appointment->service;
            $c = $appointment->customer;

            $appointmentData = [
                'id' => $appointment->id,
                'booking_reference' => $appointment->booking_reference ?: ('CC-'.str_pad((string) $appointment->id, 5, '0', STR_PAD_LEFT)),
                'amount' => $appointment->grand_total ?: ($s ? $s->price : 30000),
                'status' => $appointment->status->value ?? (string) $appointment->status,
                'payment_status' => $appointment->payment_status->value ?? (string) $appointment->payment_status,
                'deposit_paid' => (bool) $appointment->deposit_paid,
                'appointment_date' => $appointment->appointment_date?->format('Y-m-d'),
                'appointment_time' => $appointment->appointment_time,
                'service' => $s ? [
                    'id' => $s->id,
                    'name' => $s->name,
                    'price' => $s->price,
                ] : null,
                'barber' => $b ? [
                    'id' => $b->id,
                    'name' => $bu ? $bu->name : 'Master Barber',
                    'chair_number' => 1,
                    'avatar' => $bu ? $bu->avatar : null,
                ] : null,
                'customer' => $c ? [
                    'id' => $c->id,
                    'name' => $c->name,
                    'email' => $c->email,
                ] : null,
            ];
        }

        return ApiResponse::success([
            'bank_details' => [
                'bank_name' => $dbBankName,
                'account_name' => $dbAccountName,
                'account_number' => $dbAccountNumber,
                'currency' => 'NGN',
            ],
            'payment_settings' => [
                'gateway_title' => $gatewayTitle,
                'expiry_minutes' => $expiryMinutes,
                'notice_text' => $noticeText,
                'enabled_channels' => is_array($channels) ? $channels : ['bank_transfer', 'ussd', 'bank_account', 'nqr', 'card'],
                'ussd_code_template' => $ussdTemplate,
                'auto_confirm' => $autoConfirm,
            ],
            'barbers' => $barbers,
            'services' => $services,
            'appointment' => $appointmentData,
            'paystack' => [
                'public_key' => Setting::query()->where('key', 'paystack_public_key')->value('value')
                    ?: config('services.paystack.public_key')
                    ?: config('payments.gateways.paystack.public_key', ''),
                'default_gateway' => Setting::query()->where('key', 'payment_default_gateway')->value('value')
                    ?: config('payments.default', 'paystack'),
            ],
        ], 'Payment checkout data retrieved successfully.');
    }

    public function confirmTransfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'appointment_id' => 'nullable|integer|exists:appointments,id',
            'barber_id' => 'nullable|integer|exists:barbers,id',
            'service_id' => 'nullable|integer|exists:services,id',
            'amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:100',
            'sender_name' => 'nullable|string|max:150',
            'customer_name' => 'nullable|string|max:150',
            'customer_email' => 'nullable|email|max:150',
            'customer_phone' => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        $appointmentId = $validated['appointment_id'] ?? null;
        $barberId = $validated['barber_id'] ?? null;
        $serviceId = $validated['service_id'] ?? null;
        $paymentMethod = $validated['payment_method'] ?? 'bank_transfer';
        $providedRef = $validated['reference'] ?? null;

        $autoConfirm = filter_var(Setting::query()->where('key', 'payment_auto_confirm')->value('value') ?? true, FILTER_VALIDATE_BOOLEAN);
        $apptStatus = $autoConfirm ? AppointmentStatus::confirmed->value : AppointmentStatus::pending->value;
        $apptPaymentStatus = $autoConfirm ? AppointmentPaymentStatus::paid : AppointmentPaymentStatus::unpaid;
        $apptDepositPaid = $autoConfirm;
        $paymentStatus = $autoConfirm ? PaymentStatus::successful->value : PaymentStatus::awaiting_verification->value;
        $gatewayMsg = $autoConfirm ? 'Successful bank transfer verified by customer' : 'Customer submitted transfer; awaiting admin verification';

        /** @var array{already_paid: bool, appointment: Appointment, payment: Payment, reference: string, effective_auto_confirm: bool} $txResult */
        $txResult = DB::transaction(function () use (
            $appointmentId,
            $barberId,
            $serviceId,
            $paymentMethod,
            $providedRef,
            $validated,
            $user,
            $autoConfirm,
            $apptStatus,
            $apptPaymentStatus,
            $apptDepositPaid,
            $paymentStatus,
            $gatewayMsg,
            $request
        ) {
            /** @var Appointment|null $appointment */
            $appointment = null;

            if ($appointmentId) {
                $appointment = Appointment::with(['barber', 'service', 'customer', 'payment'])
                    ->lockForUpdate()
                    ->find($appointmentId);

                if ($appointment && $appointment->payment_status === AppointmentPaymentStatus::paid && $appointment->payment?->status?->isSuccess()) {
                    $existingPayment = $appointment->payment;

                    return [
                        'already_paid' => true,
                        'appointment' => $appointment,
                        'payment' => $existingPayment,
                        'reference' => (string) ($existingPayment->reference ?: $existingPayment->transaction_ref ?: $appointment->booking_reference),
                        'effective_auto_confirm' => true,
                    ];
                }
            }

            // If no existing appointment, create or resolve appointment on the fly
            if (! $appointment) {
                $barber = $barberId ? Barber::find($barberId) : Barber::first();
                $service = $serviceId ? Service::find($serviceId) : Service::first();
                $branchId = $barber?->branch_id
                    ?? Branch::query()->where('is_active', true)->value('id')
                    ?? Branch::query()->value('id');

                if (! $branchId) {
                    $business = Business::query()->first() ?? Business::create([
                        'name' => 'CandyCutz Grooming Lounge',
                        'legal_name' => 'CandyCutz Grooming Ltd.',
                        'phone' => '+234 810 000 0000',
                        'email' => 'concierge@candycutz.com',
                        'logo' => '/images/logo.png',
                    ]);

                    $branch = Branch::firstOrCreate(
                        ['slug' => 'keffi-central'],
                        [
                            'business_id' => $business->id,
                            'name' => 'Keffi Main Hub',
                            'address' => 'Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments, BCG, Keffi 961101, Nasarawa, Nigeria',
                            'latitude' => 8.8486,
                            'longitude' => 7.8736,
                            'phone' => '+234 810 000 0000',
                            'email' => 'keffi@candycutz.com',
                            'is_active' => true,
                        ]
                    );

                    $branchId = $branch->id;
                }

                $price = (int) ($validated['amount'] ?? ($service ? $service->price : 30000));
                $bookingRef = 'CC-'.strtoupper(Str::random(8));

                $appointment = Appointment::create([
                    'booking_reference' => $bookingRef,
                    'branch_id' => $branchId,
                    'customer_id' => $user ? $user->id : 1,
                    'barber_id' => $barber ? $barber->id : 1,
                    'service_id' => $service ? $service->id : 1,
                    'appointment_type' => 'in_salon',
                    'appointment_date' => now()->toDateString(),
                    'appointment_time' => '10:00:00',
                    'end_time' => '11:00:00',
                    'total_amount' => $price,
                    'grand_total' => $price,
                    'total_price' => $price,
                    'client_name' => $validated['customer_name'] ?? ($user ? $user->name : 'Walk-In Customer'),
                    'client_email' => $validated['customer_email'] ?? ($user ? $user->email : 'customer@candycutz.com'),
                    'client_phone' => $validated['customer_phone'] ?? '08012345678',
                    'status' => $apptStatus,
                    'payment_status' => $apptPaymentStatus,
                    'deposit_paid' => $apptDepositPaid,
                ]);
            } else {
                // Update selected barber if supplied
                if ($barberId && $appointment->barber_id !== (int) $barberId) {
                    $appointment->barber_id = (int) $barberId;
                }

                $appointment->update([
                    'status' => $apptStatus,
                    'payment_status' => $apptPaymentStatus,
                    'deposit_paid' => $apptDepositPaid,
                ]);
            }

            $amountKobo = (int) ($validated['amount'] ?? ($appointment->grand_total ?: ($appointment->service ? $appointment->service->price : 30000)));
            $reference = $providedRef ?: ($appointment->booking_reference ?: 'PAY-'.strtoupper(Str::random(10)));

            /** @var Payment|null $payment */
            $payment = $appointment->payment;
            if (! $payment) {
                $payment = Payment::create([
                    'appointment_id' => $appointment->id,
                    'customer_id' => $appointment->customer_id ?? ($user?->id ?? 1),
                    'amount' => $amountKobo,
                    'amount_minor' => $amountKobo * 100,
                    'currency' => 'NGN',
                    'status' => $paymentStatus,
                    'provider' => 'manual_transfer',
                    'payment_method' => $paymentMethod,
                    'channel' => $paymentMethod,
                    'reference' => $reference,
                    'transaction_ref' => $reference,
                    'paid_at' => now(),
                    'verified_at' => $autoConfirm ? now() : null,
                    'verified_by_user_id' => $autoConfirm ? $user?->id : null,
                    'gateway_response' => $gatewayMsg,
                ]);
            } else {
                $payment->update([
                    'status' => $paymentStatus,
                    'amount' => $amountKobo,
                    'amount_minor' => $amountKobo * 100,
                    'payment_method' => $paymentMethod,
                    'channel' => $paymentMethod,
                    'reference' => $reference,
                    'transaction_ref' => $reference,
                    'paid_at' => now(),
                    'verified_at' => $autoConfirm ? now() : null,
                    'verified_by_user_id' => $autoConfirm ? $user?->id : null,
                    'gateway_response' => $gatewayMsg,
                ]);
            }

            // Ledger transaction entry
            PaymentTransaction::firstOrCreate(
                ['idempotency_key' => "transfer:paid:{$appointment->id}:{$reference}"],
                [
                    'payment_id' => $payment->id,
                    'transaction_type' => 'capture',
                    'gateway' => $paymentMethod,
                    'gateway_event_id' => "tx_{$reference}",
                    'amount' => $amountKobo,
                    'amount_minor' => $amountKobo * 100,
                    'status' => 'successful',
                    'created_at' => now(),
                ]
            );

            // Status history for appointment
            try {
                AppointmentStatusHistory::create([
                    'appointment_id' => $appointment->id,
                    'previous_status' => 'pending',
                    'new_status' => AppointmentStatus::confirmed->value,
                    'changed_by_user_id' => $user?->id ?? $appointment->customer_id ?? 1,
                    'reason' => "Payment completed via {$paymentMethod} (Ref: {$reference})",
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Could not record status history: '.$e->getMessage());
            }

            // Audit log for admin/superadmin compliance
            $auditUserId = $user ? $user->id : ($appointment->customer_id ?? 1);
            $auditRole = $user ? ($user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role) : 'customer';

            try {
                AuditLog::create([
                    'user_id' => $auditUserId,
                    'user_role' => $auditRole,
                    'action' => 'payment.transfer_completed',
                    'module' => 'Payment',
                    'target_type' => Appointment::class,
                    'target_id' => $appointment->id,
                    'new_value' => [
                        'payment_id' => $payment->id,
                        'barber_id' => $appointment->barber_id,
                        'amount' => $amountKobo,
                        'payment_method' => $paymentMethod,
                        'reference' => $reference,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Could not record audit log: '.$e->getMessage());
            }

            return [
                'already_paid' => false,
                'appointment' => $appointment->fresh(),
                'payment' => $payment->fresh(),
                'reference' => $reference,
                'effective_auto_confirm' => $autoConfirm,
            ];
        }, 3);

        $appointment = $txResult['appointment'];
        $payment = $txResult['payment'];
        $reference = $txResult['reference'];
        $effectiveAutoConfirm = $txResult['effective_auto_confirm'];

        // Dispatch notifications & official booking/payment email receipt only on first confirmation
        if (! $txResult['already_paid']) {
            try {
                if ($effectiveAutoConfirm) {
                    PaymentConfirmed::dispatch($payment, $appointment);
                } else {
                    app(NotificationDispatcher::class)->paymentReceived($appointment);
                }
            } catch (\Throwable $e) {
                Log::warning('Could not dispatch notification: '.$e->getMessage());
            }
        }

        $appointment->load(['barber.user', 'service', 'customer']);

        $b = $appointment->barber;
        $bu = $b ? $b->user : null;

        return ApiResponse::success([
            'payment' => new PaymentResource($payment),
            'appointment' => [
                'id' => $appointment->id,
                'booking_reference' => $appointment->booking_reference,
                'verification_code' => $appointment->verification_code,
                'status' => $appointment->status->value ?? (string) $appointment->status,
                'payment_status' => $appointment->payment_status->value ?? (string) $appointment->payment_status,
                'deposit_paid' => (bool) $appointment->deposit_paid,
                'total_amount' => $appointment->total_amount,
                'appointment_date' => $appointment->appointment_date?->format('Y-m-d'),
                'appointment_time' => $appointment->appointment_time,
                'total_duration_minutes' => (int) ($appointment->total_duration_minutes ?: $appointment->service?->duration_minutes ?: 30),
                'service' => $appointment->service ? [
                    'id' => $appointment->service->id,
                    'name' => $appointment->service->name,
                    'price' => (float) $appointment->service->price,
                    'duration_minutes' => (int) ($appointment->service->duration_minutes ?? 30),
                ] : null,
            ],
            'barber' => [
                'id' => $b ? $b->id : null,
                'name' => $bu ? $bu->name : 'Master Barber',
                'chair_number' => 1,
                'avatar' => $bu ? $bu->avatar : null,
            ],
            'reference' => $reference,
            'payment_status' => $effectiveAutoConfirm ? 'paid' : 'awaiting_verification',
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'message' => $effectiveAutoConfirm
                ? 'Payment has been processed, saved to database, and confirmed for both Admin and Barber.'
                : 'Transfer submitted and awaiting verification by CandyCutz staff.',
        ], $effectiveAutoConfirm ? 'Payment completed successfully.' : 'Transfer recorded and awaiting verification.');
    }
}
