<?php

namespace App\Modules\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $serviceCategoryId = $this->route('serviceCategory')?->id ?? $this->route('serviceCategory');

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', Rule::unique('service_categories', 'slug')->ignore($serviceCategoryId)],
            'icon' => ['nullable', 'string', 'max:100'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}