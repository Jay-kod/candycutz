<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRefundPolicyRequest extends FormRequest
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
            'refund_full_hours' => ['required', 'integer', 'min:1', 'max:336'],
            'refund_partial_hours' => ['required', 'integer', 'min:0', 'max:168', 'lte:refund_full_hours'],
            'refund_partial_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'refund_compassionate_enabled' => ['required', 'boolean'],
            'refund_sla_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'refund_require_evidence_for_compassionate' => ['nullable', 'boolean'],
        ];
    }
}
