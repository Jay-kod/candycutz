<?php

namespace App\Http\Requests\Api\V1\Admin;

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
            'settings.testimonials_flow_speed' => ['sometimes', 'nullable'],
            'settings.testimonials_flow_direction' => ['sometimes', 'nullable', 'in:left,right'],
            'settings.brand_name' => ['sometimes', 'string', 'max:120'],
            'settings.site_name' => ['sometimes', 'string', 'max:120'],
            'settings.brand_name_part_one' => ['sometimes', 'string', 'max:60'],
            'settings.brand_name_part_two' => ['sometimes', 'string', 'max:60'],
            'settings.brand_name_separator' => ['sometimes', 'in:none,space'],
            'settings.brand_gold_part' => ['sometimes', 'in:first,second'],
            'settings.brand_tagline' => ['sometimes', 'nullable', 'string', 'max:300'],
            'settings.site_description' => ['sometimes', 'nullable', 'string', 'max:300'],
            'hero_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'page_hero_page' => ['nullable', 'required_with:page_hero_image', 'in:home,about,services,gallery,contact,privacy,terms,legal,security,cookies,account_deletion'],
            'page_hero_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
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
