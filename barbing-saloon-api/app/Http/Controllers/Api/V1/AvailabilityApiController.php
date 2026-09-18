<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Booking\Actions\GetAvailability;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityApiController
{
    public function index(Request $request, GetAvailability $action): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'barber_id' => ['nullable', 'integer', 'exists:barbers,id'],
            'type' => ['nullable', 'string', 'in:in_shop,home_service'],
        ]);

        $timeSlots = $action->execute($validated);
        if ($timeSlots === null) {
            return ApiResponse::error('Service or Barber not found.', [], 404, 'RESOURCE_NOT_FOUND');
        }

        return ApiResponse::success($timeSlots, 'Available time slots retrieved successfully');
    }
}
