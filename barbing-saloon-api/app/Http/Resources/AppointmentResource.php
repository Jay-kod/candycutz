<?php

namespace App\Http\Resources;

use App\Domain\Shared\Enums\AppointmentSource;
use App\Domain\Shared\Services\UserMediaUrl;
use App\Models\Appointment;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Appointment
 */
class AppointmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $userMediaUrl = app(UserMediaUrl::class);
        $service = $this->service;
        $barber = $this->barber;
        $statusVal = $this->status->value ?? $this->status;
        $sourceVal = $this->source->value ?? (is_string($this->source) ? $this->source : 'web');
        $sourceLabel = $this->source instanceof AppointmentSource ? $this->source->label() : (match ($sourceVal) {
            'app' => 'Mobile App',
            'walk_in' => 'Walk-In',
            default => 'Website',
        });

        // Resolve bank transfer details
        $bankAccounts = config('payments.gateways.manual.bank_accounts');
        $firstAccount = is_array($bankAccounts) ? collect($bankAccounts)->first() : null;

        $dbBankName = Setting::query()->where('key', 'bank_name')->value('value')
            ?: Setting::query()->where('key', 'manual_transfer_bank')->value('value');
        $dbAccountName = Setting::query()->where('key', 'account_name')->value('value')
            ?: Setting::query()->where('key', 'manual_transfer_account_name')->value('value');
        $dbAccountNumber = Setting::query()->where('key', 'account_number')->value('value')
            ?: Setting::query()->where('key', 'manual_transfer_account_number')->value('value');

        $bankName = $dbBankName
            ?: ($barber?->bank_name
            ?: ($firstAccount['bank_name']
            ?? config('payments.gateways.manual_transfer.default_bank_name')));

        $accountName = $dbAccountName
            ?: ($barber?->account_name
            ?: ($firstAccount['account_name']
            ?? config('payments.gateways.manual_transfer.default_account_name')));

        $accountNumber = $dbAccountNumber
            ?: ($barber?->account_number
            ?: ($firstAccount['account_number']
            ?? config('payments.gateways.manual_transfer.default_account_number')));

        $isPlaceholder = in_array((string) $accountNumber, ['0000000000', ''], true);
        $bankDetails = (! $isPlaceholder && $bankName && $accountNumber) ? [
            'bank_name' => (string) $bankName,
            'account_name' => (string) ($accountName ?: 'Candy Cutz Saloon'),
            'account_number' => (string) $accountNumber,
        ] : null;

        $payment = $this->relationLoaded('payment') ? $this->payment : $this->payment()->first();
        $paymentStatus = 'pending';
        if ($this->deposit_paid || ($payment && in_array($payment->status?->value ?? $payment->status, ['verified', 'successful'], true))) {
            $paymentStatus = 'paid';
        } elseif ($payment && in_array($payment->status?->value ?? $payment->status, ['receipt_uploaded', 'under_review', 'awaiting_verification'], true)) {
            $paymentStatus = 'awaiting_verification';
        } elseif ($this->payment_status === 'unpaid') {
            $paymentStatus = 'pending';
        }

        $receiptUrl = $payment?->receipt_url ? url('/api/v1/payments/appointments/'.$this->id.'/receipt') : null;

        return [
            'id' => $this->id,
            'booking_reference' => $this->booking_reference ?? "CC-{$this->id}",
            'customer_id' => $this->customer_id,
            'client_name' => $this->client_name ?: ($this->customer->name ?? 'Client'),
            'client_phone' => $this->client_phone ?: ($this->customer->phone ?? 'N/A'),
            'client_email' => $this->client_email ?: ($this->customer->email ?? ''),
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
            'payment_status' => $paymentStatus,
            'deposit_paid' => (bool) $this->deposit_paid,
            'bank_details' => $bankDetails,
            'payment_method' => $payment?->payment_method ?? ($this->appointment_type === 'walk_in' ? 'pay_at_venue' : 'manual_transfer'),
            'transaction_ref' => $payment?->transaction_ref ?? $payment?->reference,
            'receipt_url' => $receiptUrl,
            'receipt_image' => $receiptUrl,
            'paid_at' => $payment?->paid_at?->toIso8601String(),
            'verified_at' => $payment?->verified_at?->toIso8601String(),
            'payment' => $payment ? [
                'id' => $payment->id,
                'status' => $payment->status instanceof \BackedEnum ? $payment->status->value : $payment->status,
                'amount' => (float) ($payment->amount ?? $this->total_price ?? 0),
                'payment_method' => $payment->payment_method,
                'reference' => $payment->reference,
                'transaction_ref' => $payment->transaction_ref,
                'receipt_url' => $receiptUrl,
                'paid_at' => $payment->paid_at?->toIso8601String(),
                'verified_at' => $payment->verified_at?->toIso8601String(),
            ] : null,
            'total_duration_minutes' => (int) ($this->total_duration_minutes ?? $service->duration_minutes ?? 30),
            'subtotal' => (float) ($this->total_price ?? $this->total_amount ?? $service->price ?? 0),
            'home_service_surcharge' => (float) ($this->travel_fee ?? 0),
            'discount_amount' => (float) ($this->discount_amount ?? 0),
            'grand_total' => (float) ($this->grand_total ?? $this->total_price ?? $service->price ?? 0),
            'total_price' => (float) ($this->total_price ?? $this->grand_total ?? $service->price ?? 0),
            'notes' => $this->notes,
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
                'avatar' => $userMediaUrl->for($this->customer, $this->customer->avatar),
                'avatar_url' => $userMediaUrl->for($this->customer, $this->customer->avatar),
            ] : null,
            'customer_avatar' => $userMediaUrl->for($this->customer, $this->customer?->avatar),
            'customer_avatar_url' => $userMediaUrl->for($this->customer, $this->customer?->avatar),
            'service' => $service ? [
                'id' => $service->id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'duration_minutes' => (int) $service->duration_minutes,
                'category' => $service->category->name ?? 'Grooming',
            ] : null,
            'barber' => $barber ? [
                'id' => $barber->id,
                'name' => $barber->user->name ?? 'Master Barber',
                'username' => $barber->user->username ?? 'barber',
                'avatar' => $userMediaUrl->for($barber->user, $barber->user?->avatar),
                'avatar_url' => $userMediaUrl->for($barber->user, $barber->user?->avatar),
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
