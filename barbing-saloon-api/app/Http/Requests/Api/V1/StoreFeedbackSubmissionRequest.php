<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->value === 'customer';
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'in:feature_request,maintenance,improvement'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
        ];
    }
}
