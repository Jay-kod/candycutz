<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class DecideRefundRequest extends FormRequest
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
            'decision' => ['required', 'string', 'in:approve,reject'],
            'note' => ['nullable', 'string', 'max:1000'],
            'refund_amount_minor' => ['nullable', 'integer', 'min:100'],
        ];
    }
}
