<?php

declare(strict_types=1);

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Services\PaymentService;
use App\Models\Appointment;
use App\Models\User;

class InitiateCheckout
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Initialize a payment checkout for an appointment.
     *
     * @return array<string, mixed>
     */
    public function execute(User $customer, Appointment $appointment, string $paymentMethod): array
    {
        $checkoutDetails = $this->paymentService->initializePayment($appointment, $paymentMethod);

        return array_merge($checkoutDetails, [
            'payment_id' => $checkoutDetails['payment_id'] ?? null,
            'transaction_ref' => $checkoutDetails['transaction_ref'] ?? null,
        ]);
    }
}