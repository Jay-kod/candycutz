<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use App\Domain\Identity\Services\ConsentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        if ($this->has('phone') && is_string($this->phone)) {
            $merge['phone'] = preg_replace('/[\s\-\(\)]+/', '', $this->phone);
        }
        if ($this->has('username') && is_string($this->username)) {
            $merge['username'] = ltrim(strtolower(trim($this->username)), '@');
        }
        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'username' => ['nullable', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:users,username'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'min:7', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'device_name' => ['nullable', 'string', 'max:50'],
            'terms_accepted' => ['required', 'accepted'],
            'terms_version' => ['nullable', 'string', 'max:40'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $submitted = $this->input('terms_version');
                $current = app(ConsentService::class)->currentTermsVersion();

                if (is_string($submitted) && $submitted !== '' && $submitted !== $current) {
                    $validator->errors()->add(
                        'terms_version',
                        'The Terms of Service have changed. Please review and accept the current version.'
                    );
                }
            },
        ];
    }
}
