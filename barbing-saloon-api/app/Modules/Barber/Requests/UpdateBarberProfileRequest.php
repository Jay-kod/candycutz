<?php

namespace App\Modules\Barber\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBarberProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:50'],
        ];
    }
}