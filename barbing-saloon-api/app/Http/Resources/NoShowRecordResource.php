<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Payment\Services\PricingService;
use App\Models\NoShowRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NoShowRecord
 */
class NoShowRecordResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $appointment = $this->appointment;
        $payment = $appointment->payment;
        $pricing = app(PricingService::class);
        $paidNaira = $pricing->resolvedNairaAmount($appointment, $payment);
        $paymentStatus = $payment?->status;
        $paymentStatusVal = $paymentStatus instanceof \BackedEnum
            ? $paymentStatus->value
            : (is_string($paymentStatus) ? $paymentStatus : null);
        $apptPaymentStatusVal = $appointment->payment_status->value;

        $isPaid = (bool) ($appointment->deposit_paid || ($payment && $payment->status->isSuccess()));
        $isRefunded = in_array($paymentStatusVal, ['refunded'], true) || in_array($apptPaymentStatusVal, ['refunded', 'refund_pending'], true);

        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'customer_id' => $this->customer_id,
            'marked_at' => $this->marked_at?->toIso8601String(),
            'marked_by' => $this->marked_by,
            'marked_by_name' => $this->markedBy?->name,
            'reason' => $this->reason,
            'was_automatic' => (bool) $this->was_automatic,
            'deposit_forfeited' => (bool) $this->deposit_forfeited,
            'deposit_forfeited_minor' => (int) $this->deposit_forfeited_minor,
            'deposit_forfeited_amount' => round($this->deposit_forfeited_minor / 100, 2),
            'deposit_forfeited_at' => $this->deposit_forfeited_at?->toIso8601String(),
            'forfeited_by_name' => $this->forfeitedBy?->name,
            'grace_extended' => (bool) $this->grace_extended,
            'grace_reason' => $this->grace_reason,
            'appealed' => (bool) $this->appealed,
            'appeal_reason' => $this->appeal_reason,
            'appealed_at' => $this->appealed_at?->toIso8601String(),
            'appeal_outcome' => $this->appeal_outcome,
            'resolved_by' => $this->resolved_by,
            'resolved_by_name' => $this->resolvedBy?->name,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'resolution_note' => $this->resolution_note,
            'can_forfeit_deposit' => $isPaid && ! $this->deposit_forfeited && ! $isRefunded,
            'can_initiate_refund' => $isPaid && ! $isRefunded && ($payment !== null && $payment->status?->isSuccess()),
            'paid_amount_naira' => $isPaid ? $paidNaira : 0.0,
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
                'no_show_count' => (int) ($this->customer->no_show_count ?? 0),
                'requires_deposit' => (bool) ($this->customer->requires_deposit ?? false),
            ] : null,
            'appointment' => $appointment ? [
                'id' => $appointment->id,
                'booking_reference' => $appointment->booking_reference ?? "CC-{$appointment->id}",
                'client_name' => $appointment->client_name,
                'client_phone' => $appointment->client_phone,
                'client_email' => $appointment->client_email,
                'appointment_date' => $appointment->appointment_date?->toDateString(),
                'appointment_time' => substr((string) $appointment->appointment_time, 0, 5),
                'deposit_paid' => (bool) $appointment->deposit_paid,
                'payment_status' => $apptPaymentStatusVal ?? 'unpaid',
                'service_name' => $appointment->service?->name,
                'barber_name' => $appointment->barber?->user?->name,
                'payment' => $payment ? [
                    'id' => $payment->id,
                    'provider' => $payment->provider,
                    'payment_method' => $payment->payment_method,
                    'status' => $paymentStatusVal,
                    'amount' => $paidNaira,
                    'reference' => $payment->reference ?: $payment->transaction_ref,
                ] : null,
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
