<?php

declare(strict_types=1);

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\ManualTransferState;
use App\Domain\Payment\Services\ManualTransferStateMachine;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class UploadReceipt
{
    public function __construct(
        protected ManualTransferStateMachine $stateMachine
    ) {}

    public function execute(Appointment $appointment, UploadedFile $file): Payment
    {
        // 1. Validate file manually (if not done in FormRequest)
        $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (! in_array($file->getMimeType(), $allowedMimes)) {
            throw ValidationException::withMessages([
                'receipt' => ['Receipt must be a JPG, PNG, or PDF.'],
            ]);
        }

        if ($file->getSize() > 5120 * 1024) {
            throw ValidationException::withMessages([
                'receipt' => ['Receipt may not be greater than 5MB.'],
            ]);
        }

        // 2. Store to private disk securely
        $path = $file->store("receipts/{$appointment->id}", 'local');

        // 3. Find or initialize payment
        $payment = Payment::firstOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'customer_id' => $appointment->customer_id ?? 1,
                'amount' => $appointment->grand_total ?: $appointment->total_price,
                'currency' => 'NGN',
                'status' => ManualTransferState::awaiting_transfer->value,
                'payment_method' => 'manual_transfer',
                'transaction_ref' => 'MANUAL_'.strtoupper(uniqid()),
            ]
        );

        // 4. Transition through state machine: receipt_uploaded -> under_review
        $payment = $this->stateMachine->transition(
            $payment,
            ManualTransferState::receipt_uploaded,
            ['receipt_path' => $path, 'actor_id' => $appointment->customer_id]
        );

        return $this->stateMachine->transition(
            $payment,
            ManualTransferState::under_review,
            ['actor_id' => $appointment->customer_id]
        );
    }
}
