<?php

declare(strict_types=1);

namespace App\Domain\Booking\DataObjects;

use Illuminate\Http\Request;
use Carbon\Carbon;

final class BookingData
{
    public function __construct(
        public readonly int $serviceId,
        public readonly ?int $barberId,
        public readonly string $appointmentDate,
        public readonly string $appointmentTime,
        public readonly ?string $appointmentType,
        public readonly ?array $destinationAddress,
        public readonly ?string $paymentMethod,
        public readonly ?string $notes,
        public readonly ?string $clientName = null,
        public readonly ?string $clientPhone = null,
        public readonly ?bool $takeImmediately = false
    ) {}

    public static function fromRequest(Request $request): self
    {
        // Allow start_time or appointment_time
        $appointmentTime = $request->input('appointment_time', $request->input('start_time'));

        return new self(
            serviceId: (int) $request->input('service_id'),
            barberId: $request->filled('barber_id') ? (int) $request->input('barber_id') : null,
            appointmentDate: $request->input('appointment_date', Carbon::today()->toDateString()),
            appointmentTime: (string) $appointmentTime,
            appointmentType: $request->input('appointment_type'),
            destinationAddress: $request->input('destination_address'),
            paymentMethod: $request->input('payment_method'),
            notes: $request->input('notes'),
            clientName: $request->input('client_name', $request->input('customer_name')),
            clientPhone: $request->input('client_phone', $request->input('customer_phone')),
            takeImmediately: $request->boolean('take_immediately')
        );
    }
}
