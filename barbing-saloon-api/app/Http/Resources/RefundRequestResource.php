<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RefundRequest */
class RefundRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $appointment = $this->relationLoaded('appointment') ? $this->appointment : null;
        $customer = $this->relationLoaded('customer') ? $this->customer : null;
        $decider = $this->relationLoaded('decider') ? $this->decider : null;
        $payment = $this->relationLoaded('payment') ? $this->payment : null;

        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'payment_id' => $this->payment_id,
            'customer_id' => $this->customer_id,
            'type' => $this->type,
            'reason_category' => $this->reason_category,
            'reason_text' => $this->reason_text,
            'has_evidence' => ! empty($this->supporting_evidence),
            'evidence_url' => ! empty($this->supporting_evidence)
                ? url("/api/v1/refunds/{$this->id}/evidence")
                : null,
            'refund_amount_minor' => (int) $this->refund_amount_minor,
            'refund_amount' => round($this->refund_amount_minor / 100, 2),
            'original_amount_minor' => (int) $this->original_amount_minor,
            'original_amount' => round($this->original_amount_minor / 100, 2),
            'refund_percentage' => (int) $this->refund_percentage,
            'status' => $this->status,
            'decided_by' => $this->decided_by,
            'decided_at' => $this->decided_at?->toIso8601String(),
            'decision_note' => $this->decision_note,
            'gateway_refund_ref' => $this->gateway_refund_ref,
            'refunded_at' => $this->refunded_at?->toIso8601String(),
            'can_decide' => in_array($this->status, ['pending', 'failed'], true),
            'can_complete_manual' => $this->status === 'processing',
            'customer' => $customer ? [
                'id' => $customer->id,
                'name' => $customer->real_name ?: $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ] : null,
            'decider' => $decider ? [
                'id' => $decider->id,
                'name' => $decider->name,
            ] : null,
            'payment' => $payment ? [
                'id' => $payment->id,
                'provider' => $payment->provider ?: $payment->payment_method,
                'payment_method' => $payment->payment_method,
                'reference' => $payment->reference ?: $payment->transaction_ref,
            ] : null,
            'appointment' => $appointment ? [
                'id' => $appointment->id,
                'booking_reference' => $appointment->booking_reference,
                'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                'appointment_time' => $appointment->appointment_time,
                'status' => $appointment->status instanceof \BackedEnum ? $appointment->status->value : $appointment->status,
                'payment_status' => $appointment->payment_status instanceof \BackedEnum ? $appointment->payment_status->value : $appointment->payment_status,
                'service_name' => $appointment->service?->name,
                'barber_name' => $appointment->barber?->user?->name,
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
