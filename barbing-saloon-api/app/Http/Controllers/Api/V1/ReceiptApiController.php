<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReceiptApiController
{
    public function show(Request $request, int $appointmentId): BinaryFileResponse
    {
        $appointment = Appointment::with(['payment', 'barber'])->findOrFail($appointmentId);
        $user = $request->user();
        $role = $user->role instanceof \BackedEnum ? $user->role->value : $user->role;
        $isStaff = in_array($role, ['admin', 'super_admin'], true);
        $isCustomer = $appointment->customer_id === $user->id;
        $isAssignedBarber = $appointment->barber?->user_id === $user->id;

        if (! $isCustomer && ! $isAssignedBarber && ! $isStaff) {
            abort(403);
        }

        $receiptPath = (string) ($appointment->payment?->receipt_image ?: $appointment->payment?->receipt_url);
        $filePath = storage_path('app/'.$receiptPath);

        if ($receiptPath === '' || ! is_file($filePath)) {
            abort(404);
        }

        return response()->file($filePath, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
