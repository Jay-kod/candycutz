<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'barber_id' => ['nullable', 'integer', 'exists:barbers,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'string'],
            'appointment_type' => ['nullable', 'string', 'in:in_shop,home_service'],
            'destination_address' => ['nullable', 'array'],
            'payment_method' => ['nullable', 'string', 'in:pay_at_venue,stripe,wallet'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('start_time') && ! $this->has('appointment_time')) {
            $this->merge(['appointment_time' => $this->start_time]);
        }
    }
}
