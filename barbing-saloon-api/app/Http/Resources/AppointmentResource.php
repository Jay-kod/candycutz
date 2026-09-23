<?php

namespace App\Http\Resources;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Appointment
 */
class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $service = $this->service;
        $barber = $this->barber;
        $statusVal = $this->status?->value ?? $this->status;
        $sourceVal = $this->source?->value ?? (is_string($this->source) ? $this->source : 'web');
        $sourceLabel = $this->source instanceof \App\Domain\Shared\Enums\AppointmentSource ? $this->source->label() : (match ($sourceVal) {
            'app' => 'Mobile App',
            'walk_in' => 'Walk-In',
            default => 'Website',
        });

        return [
            'id' => $this->id,
            'booking_reference' => $this->booking_reference ?? "CC-{$this->id}",
            'customer_id' => $this->customer_id,
            'client_name' => $this->client_name ?: ($this->customer?->name ?? 'Client'),
            'client_phone' => $this->client_phone ?: ($this->customer?->phone ?? 'N/A'),
            'client_email' => $this->client_email ?: ($this->customer?->email ?? ''),
            'appointment_date' => Carbon::parse($this->appointment_date)->toDateString(),
            'start_time' => substr((string) $this->appointment_time, 0, 5),
            'appointment_time' => substr((string) $this->appointment_time, 0, 5),
            'end_time' => $this->end_time ? substr((string) $this->end_time, 0, 5) : null,
            'appointment_type' => $this->appointment_type ?? 'in_shop',
            'booking_type' => (str_contains($this->client_name ?? '', 'Walk-In') || $this->customer_id === null) ? 'walk_in' : 'online',
            'source' => $sourceVal,
            'source_label' => $sourceLabel,
            'status' => $statusVal,
            'verification_code' => $this->verification_code,
            'payment_status' => $this->deposit_paid ? 'paid' : 'pending',
            'deposit_paid' => (bool) $this->deposit_paid,
            'total_duration_minutes' => (int) ($this->total_duration_minutes ?? $service?->duration_minutes ?? 30),
            'subtotal' => (float) ($this->total_price ?? $this->total_amount ?? $service?->price ?? 0),
            'home_service_surcharge' => (float) ($this->travel_fee ?? 0),
            'discount_amount' => (float) ($this->discount_amount ?? 0),
            'grand_total' => (float) ($this->grand_total ?? $this->total_price ?? $service?->price ?? 0),
            'total_price' => (float) ($this->total_price ?? $this->grand_total ?? $service?->price ?? 0),
            'notes' => $this->notes,
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
                'avatar' => $this->customer->avatar,
            ] : null,
            'customer_avatar' => $this->customer?->avatar,
            'service' => $service ? [
                'id' => $service->id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'duration_minutes' => (int) $service->duration_minutes,
                'category' => $service->category?->name ?? 'Grooming',
            ] : null,
            'barber' => $barber ? [
                'id' => $barber->id,
                'name' => $barber->user?->name ?? 'Master Barber',
                'username' => $barber->user?->username ?? 'barber',
                'rating' => (float) ($barber->rating ?? 5.0),
                'chair_status' => $barber->chair_status ?? 'free',
            ] : null,
            'service_zone' => $this->serviceZone ? [
                'id' => $this->serviceZone->id,
                'name' => $this->serviceZone->name,
                'surcharge' => (float) ($this->serviceZone->base_travel_fee ?? 0),
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
