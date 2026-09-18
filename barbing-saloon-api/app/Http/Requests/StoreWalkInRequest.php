<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWalkInRequest extends FormRequest
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
            'client_name' => ['nullable', 'string', 'max:100'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'client_phone' => ['nullable', 'string', 'max:30'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'barber_id' => ['nullable', 'integer', 'exists:barbers,id'],
            'appointment_date' => ['nullable', 'date'],
            'appointment_time' => ['nullable', 'string'],
            'start_time' => ['nullable', 'string'],
            'take_immediately' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
