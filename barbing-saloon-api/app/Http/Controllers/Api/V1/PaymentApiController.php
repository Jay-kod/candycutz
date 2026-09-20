<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Payment\Actions\ConfirmPayment;
use App\Domain\Payment\Actions\InitiateCheckout;
use App\Domain\Payment\Actions\UploadReceipt;
use App\Domain\Payment\Actions\VerifyReceipt;
use App\Domain\Payment\Services\PaymentService;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentApiController
{
    public function __construct(
        protected InitiateCheckout $initiateCheckout,
        protected ConfirmPayment $confirmPayment,
        protected UploadReceipt $uploadReceiptAction
    ) {}

    public function checkout(Request $request): JsonResponse
    {
        $user = $request->user();
        $appointmentId = $request->input('appointment_id');
        $paymentMethod = $request->input('payment_method', config('payments.default'));

        $appointment = Appointment::where('id', $appointmentId)->where('customer_id', $user->id)->firstOrFail();

        $checkoutDetails = $this->initiateCheckout->execute($user, $appointment, $paymentMethod);

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

        return ApiResponse::success([
            'bank_name' => $firstAccount['bank_name'] ?? ($barber?->bank_name ?: 'Unknown Bank'),
            'account_name' => $firstAccount['account_name'] ?? ($barber?->account_name ?: 'Candy Cutz Saloon'),
            'account_number' => $firstAccount['account_number'] ?? ($barber?->account_number ?: '0000000000'),
        ], 'Payment details loaded');
    }

    public function uploadReceipt(Request $request, int $appointmentId): JsonResponse
    {
        $user = $request->user();
        $appointment = Appointment::where('id', $appointmentId)->where('customer_id', $user->id)->firstOrFail();

        $request->validate([
            'receipt' => 'required|file|max:5120',
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

        if ($approve && in_array($appointment->status?->value ?? $appointment->status, [AppointmentStatus::pending->value], true)) {
            $appointment->update(['status' => AppointmentStatus::confirmed->value]);
        }

        // Dispatch notification to customer
        try {
            if ($approve) {
                app(NotificationDispatcher::class)->paymentVerified($appointment);
            }
        } catch (\Throwable $e) {
            Log::warning('Could not dispatch paymentVerified notification: '.$e->getMessage());
        }

        return ApiResponse::success(
            new PaymentResource($updatedPayment),
            $approve ? 'Payment verified successfully.' : 'Payment rejected.'
        );
    }
}
