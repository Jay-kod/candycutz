<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptApiController
{
    /**
     * Show receipt using standard Bearer authentication.
     */
    public function show(Request $request, int $appointmentId): StreamedResponse
    {
        $appointment = Appointment::with(['payment', 'barber'])->findOrFail($appointmentId);
        $user = $request->user();

        $this->authorizeReceiptAccess($appointment, $user);

        return $this->serveReceipt($appointment);
    }

    /**
     * Show receipt via tamper-proof temporary signed URL (no Authorization header required).
     */
    public function showSigned(Request $request, int $appointmentId): StreamedResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired receipt signature.');
        }

        $appointment = Appointment::with(['payment', 'barber'])->findOrFail($appointmentId);

        return $this->serveReceipt($appointment);
    }

    /**
     * Generate a 15-minute temporary signed streaming URL for the receipt.
     */
    public function signedUrl(Request $request, int $appointmentId): JsonResponse
    {
        $appointment = Appointment::with(['payment', 'barber'])->findOrFail($appointmentId);
        $user = $request->user();

        $this->authorizeReceiptAccess($appointment, $user);

        $receiptPath = (string) ($appointment->payment?->receipt_image ?: $appointment->payment?->receipt_url);
        $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, storage_path('app/'.ltrim($receiptPath, '/\\')));

        if ($receiptPath === '' || ! file_exists($filePath) || ! is_file($filePath)) {
            return ApiResponse::error('Receipt file not found for this appointment.', [], 404);
        }

        $signedUrl = URL::temporarySignedRoute(
            'api.v1.payments.receipt.signed',
            now()->addMinutes(15),
            ['appointmentId' => $appointmentId]
        );

        return ApiResponse::success([
            'signed_url' => $signedUrl,
            'expires_in_minutes' => 15,
            'expires_at' => now()->addMinutes(15)->toIso8601String(),
        ], 'Temporary signed receipt URL generated');
    }

    protected function authorizeReceiptAccess(Appointment $appointment, ?User $user): void
    {
        if (! $user) {
            abort(401);
        }

        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
        $isStaff = in_array($role, ['admin', 'super_admin'], true);
        $isCustomer = $appointment->customer_id === $user->id;
        $isAssignedBarber = $appointment->barber?->user_id === $user->id;

        if (! $isCustomer && ! $isAssignedBarber && ! $isStaff) {
            abort(403, 'Unauthorized access to appointment receipt.');
        }
    }

    protected function serveReceipt(Appointment $appointment): StreamedResponse
    {
        $receiptPath = (string) ($appointment->payment?->receipt_image ?: $appointment->payment?->receipt_url);
        $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, storage_path('app/'.ltrim($receiptPath, '/\\')));

        if ($receiptPath === '' || ! file_exists($filePath) || ! is_file($filePath)) {
            abort(404, 'Receipt file does not exist on storage.');
        }

        $fileName = basename($filePath);
        $fileSize = filesize($filePath);
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = match ($ext) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return response()->streamDownload(function () use ($filePath) {
            $stream = fopen($filePath, 'rb');
            if ($stream !== false) {
                fpassthru($stream);
                fclose($stream);
            }
        }, $fileName, [
            'Content-Type' => $mimeType,
            'Content-Length' => (string) $fileSize,
            'Content-Disposition' => 'inline; filename="receipt-'.$fileName.'"',
            'Cache-Control' => 'private, no-store, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
