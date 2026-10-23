<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use App\Domain\Content\Services\AnimationSettingsService;
use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnimationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        return $user->can('update', Setting::class) || in_array($user->role?->value ?? $user->role, ['admin', 'super_admin'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'default_type' => ['required', 'string', Rule::in(AnimationSettingsService::ALLOWED_TYPES)],
            'duration_ms' => ['required', 'integer', 'min:50', 'max:3000'],
            'delay_ms' => ['required', 'integer', 'min:0', 'max:2000'],
            'easing' => ['required', 'string', Rule::in(AnimationSettingsService::ALLOWED_EASINGS)],
            'distance' => ['required', 'string', 'regex:/^[0-9]+(px|rem)$/'],
            'stagger_ms' => ['required', 'integer', 'min:0', 'max:1000'],
            'trigger_threshold' => ['required', 'numeric', 'min:0', 'max:1'],
            'root_margin' => ['required', 'string', 'max:50', 'regex:/^[-0-9pxrem% ]+$/'],
            'once' => ['required', 'boolean'],
            'disable_on_mobile' => ['required', 'boolean'],
            'overrides' => ['nullable', 'array'],
            'overrides.*' => ['nullable', 'array'],
            'overrides.*.*.type' => ['nullable', 'string', Rule::in(AnimationSettingsService::ALLOWED_OVERRIDE_TYPES)],
            'overrides.*.*.duration_ms' => ['nullable', 'integer', 'min:50', 'max:3000'],
            'overrides.*.*.delay_ms' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'overrides.*.*.stagger_ms' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'default_type.in' => 'The selected default animation type is invalid. Choose from the approved animation list.',
            'distance.regex' => 'The distance must be specified in px or rem (e.g., 30px or 2rem).',
            'root_margin.regex' => 'The root margin contains invalid CSS characters.',
        ];
    }
}
