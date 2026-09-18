<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Service::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'duration_minutes' => 'nullable|integer',
            'category_id' => 'required|integer|exists:service_categories,id',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ];
    }
}
