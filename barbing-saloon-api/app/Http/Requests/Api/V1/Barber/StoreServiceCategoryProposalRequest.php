<?php

namespace App\Http\Requests\Api\V1\Barber;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceCategoryProposalRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:100'],
        ];
    }
}
