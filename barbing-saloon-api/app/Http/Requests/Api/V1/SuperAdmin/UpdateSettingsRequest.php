<?php

namespace App\Http\Requests\Api\V1\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
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
            'settings' => ['nullable', 'array'],
            'settings.*' => ['nullable'],
            'hero_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
            'splash_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
            'onboarding_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
            'login_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
        ];
    }
}
