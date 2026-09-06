<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['nullable', 'string'],
            'identity' => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (empty($this->input('email')) && empty($this->input('identity'))) {
                $validator->errors()->add('identity', 'Please provide your email, username, or phone number.');
            }
        });
    }
}