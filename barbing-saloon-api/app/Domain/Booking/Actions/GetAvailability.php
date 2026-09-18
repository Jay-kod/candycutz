<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\Services\SlotHelper;
use App\Models\Barber;
use App\Models\Service;
use Carbon\Carbon;

class GetAvailability
{
    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, array{time: string, available: bool, barber_id: int}>|null
     */
    public function execute(array $validated): ?array
    {
        $date = Carbon::parse((string) $validated['date']);

        $serviceId = ! empty($validated['service_id'])
            ? (int) $validated['service_id']
            : (Service::where('is_active', true)->value('id') ?? 1);

        $service = Service::find($serviceId);
        if (! $service) {
            return null;
        }

        $slotHelper = new SlotHelper;
        $timeSlots = [];

        if (! empty($validated['barber_id'])) {
            $barber = Barber::find((int) $validated['barber_id']);
            if (! $barber) {
                return null;
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
            $activeBarbers = Barber::where('is_available', true)->get();
            $seenTimes = [];

            foreach ($activeBarbers as $barber) {
                $rawSlots = $slotHelper->generate($date, $barber, $service);
                foreach ($rawSlots as $slotTime) {
                    if (! isset($seenTimes[$slotTime])) {
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

        return $timeSlots;
    }
}
