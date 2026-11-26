<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Models\RefundRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestRefundRequest extends FormRequest
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
            'appointment_id' => ['required', 'integer', 'exists:appointments,id'],
            'reason_category' => ['required', 'string', Rule::in(RefundRequest::REASON_CATEGORIES)],
            'reason_text' => ['nullable', 'string', 'max:1000'],
            'is_compassionate' => ['nullable', 'boolean'],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'],
        ];
    }
}
