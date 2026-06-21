<?php

namespace App\Modules\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkingHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barber_id' => ['nullable', 'integer', 'exists:barbers,id'],
            'day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'open_time' => ['required', 'date_format:H:i:s'],
            'close_time' => ['required', 'date_format:H:i:s'],
            'is_closed' => ['sometimes', 'boolean'],
        ];
    }
}