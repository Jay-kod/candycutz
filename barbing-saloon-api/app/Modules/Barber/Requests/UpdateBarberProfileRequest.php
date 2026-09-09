<?php

namespace App\Modules\Barber\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBarberProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('specialties') && is_string($this->input('specialties'))) {
            $decoded = json_decode($this->input('specialties'), true);
            if (is_array($decoded)) {
                $this->merge(['specialties' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'instagram_url' => ['nullable', 'string', 'max:255'],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:50'],
            'profile_image' => ['nullable', 'image', 'max:5120'],
            'avatar' => ['nullable', 'image', 'max:5120'],
        ];
    }
}