<?php

declare(strict_types=1);

namespace App\Domain\Payment\Actions;

use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UploadReceipt
{
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

        // 3. Update payment status
        return DB::transaction(function () use ($appointment, $path) {
            $payment = Payment::firstOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'customer_id' => $appointment->customer_id,
                    'amount' => $appointment->total_price,
                    'currency' => 'NGN',
                    'status' => 'pending',
                    'payment_method' => 'manual_transfer',
                    'transaction_ref' => 'MANUAL_'.strtoupper(uniqid()),
                ]
            );

            $payment->update([
                'receipt_url' => $path, // This is a private storage path now, not a public URL
                'status' => 'under_review',
            ]);

            return $payment;
        });
    }
}
