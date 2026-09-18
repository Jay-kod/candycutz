<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class LoginRequest extends FormRequest
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
            'email' => ['nullable', 'string'],
            'identity' => ['nullable', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if (empty($this->input('email')) && empty($this->input('identity'))) {
                $validator->errors()->add('identity', 'Please provide your email, username, or phone number.');
            }
        });
    }
}
