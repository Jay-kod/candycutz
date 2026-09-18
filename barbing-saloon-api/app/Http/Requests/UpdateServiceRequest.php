<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $service = Service::findOrFail($this->route('id'));

        return $this->user()->can('update', $service);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric',
            'duration_minutes' => 'nullable|integer',
            'category_id' => 'sometimes|integer|exists:service_categories,id',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ];
    }
}
