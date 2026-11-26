<?php

declare(strict_types=1);

namespace App\Http\Requests\Booking;

use App\Models\RefundRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500'],
            'reason_category' => ['nullable', 'string', Rule::in(RefundRequest::REASON_CATEGORIES)],
            'request_compassionate' => ['nullable', 'boolean'],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'],
        ];
    }
}
