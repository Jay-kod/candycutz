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
            'settings.brand_name' => ['sometimes', 'string', 'max:120'],
            'settings.site_name' => ['sometimes', 'string', 'max:120'],
            'settings.brand_tagline' => ['sometimes', 'nullable', 'string', 'max:300'],
            'settings.site_description' => ['sometimes', 'nullable', 'string', 'max:300'],
            'hero_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'about_teaser_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'about_shop_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'splash_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'onboarding_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'login_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'favicon' => ['nullable', 'image', 'mimes:jpeg,png,webp,ico', 'max:5120'],
        ];
    }
}
