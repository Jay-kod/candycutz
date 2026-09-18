<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Payment\Actions\UploadReceipt;
use App\Domain\Payment\Services\PaymentService;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentApiController
{
    public function __construct(
        protected PaymentService $paymentService,
        protected UploadReceipt $uploadReceiptAction
    ) {}

    public function checkout(Request $request): JsonResponse
    {
        $user = $request->user();
        $appointmentId = $request->input('appointment_id');
        $paymentMethod = $request->input('payment_method', config('payments.default'));

        $appointment = Appointment::where('id', $appointmentId)->where('customer_id', $user->id)->firstOrFail();

        $checkoutDetails = $this->paymentService->initializePayment($appointment, $paymentMethod);

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

        return ApiResponse::success([
            'bank_name' => collect(config('payments.gateways.manual.bank_accounts'))->first()['bank_name'] ?? ($barber?->bank_name ?: 'Unknown Bank'),
            'account_name' => collect(config('payments.gateways.manual.bank_accounts'))->first()['account_name'] ?? ($barber?->account_name ?: 'Candy Cutz Saloon'),
            'account_number' => collect(config('payments.gateways.manual.bank_accounts'))->first()['account_number'] ?? ($barber?->account_number ?: '0000000000'),
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

        return ApiResponse::success(
            new PaymentResource($payment),
            'Receipt uploaded successfully. Awaiting verification.'
        );
    }
}
