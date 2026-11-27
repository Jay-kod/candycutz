<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ResolveNoShowAppealRequest extends FormRequest
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
            'outcome' => ['required', 'string', 'in:approved,rejected'],
            'resolution_note' => ['nullable', 'string', 'max:1000'],
            'initiate_refund' => ['sometimes', 'boolean'],
        ];
    }
}
