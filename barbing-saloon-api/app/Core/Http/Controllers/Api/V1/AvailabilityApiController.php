<?php

declare(strict_types=1);

namespace App\Core\Http\Controllers\Api\V1;

use App\Core\Http\Response\ApiResponse;
use App\Models\Barber;
use App\Models\Service;
use App\Modules\Landing\Services\SlotHelper;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityApiController
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'barber_id' => ['nullable', 'integer', 'exists:barbers,id'],
            'type' => ['nullable', 'string', 'in:in_shop,home_service'],
        ]);

        $date = Carbon::parse($validated['date']);

        // Default to first service if omitted
        $serviceId = !empty($validated['service_id'])
            ? (int) $validated['service_id']
            : (Service::where('is_active', true)->value('id') ?? 1);

        $service = Service::find($serviceId);
        if (!$service) {
            return ApiResponse::error('Service not found.', [], 404, 'RESOURCE_NOT_FOUND');
        }

        $slotHelper = new SlotHelper();
        $timeSlots = [];

        if (!empty($validated['barber_id'])) {
            $barber = Barber::find((int) $validated['barber_id']);
            if (!$barber) {
                return ApiResponse::error('Barber not found.', [], 404, 'RESOURCE_NOT_FOUND');
            }

            $rawSlots = $slotHelper->generate($date, $barber, $service);
            foreach ($rawSlots as $slotTime) {
                $timeSlots[] = [
                    'time' => $slotTime,
                    'available' => true,
                    'barber_id' => $barber->id,
                ];
            }
        } else {
            // Aggregate availability across all active barbers
            $activeBarbers = Barber::where('is_available', true)->get();
            $seenTimes = [];

            foreach ($activeBarbers as $barber) {
                $rawSlots = $slotHelper->generate($date, $barber, $service);
                foreach ($rawSlots as $slotTime) {
                    if (!isset($seenTimes[$slotTime])) {
                        $seenTimes[$slotTime] = true;
                        $timeSlots[] = [
                            'time' => $slotTime,
                            'available' => true,
                            'barber_id' => $barber->id,
                        ];
                    }
                }
            }

            usort($timeSlots, fn ($a, $b) => strcmp($a['time'], $b['time']));
        }

        return ApiResponse::success($timeSlots, 'Available time slots retrieved successfully');
    }
}
