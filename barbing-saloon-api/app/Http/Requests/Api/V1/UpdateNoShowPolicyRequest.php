<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoShowPolicyRequest extends FormRequest
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
            'no_show_delay_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'no_show_warning_lead_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            'no_show_grace_limit' => ['required', 'integer', 'min:0', 'max:5'],
            'no_show_grace_extension_minutes' => ['required', 'integer', 'min:5', 'max:120'],
            'no_show_threshold' => ['required', 'integer', 'min:1', 'max:20'],
            'no_show_threshold_window_days' => ['required', 'integer', 'min:7', 'max:365'],
            'no_show_auto_mark_enabled' => ['required', 'boolean'],
            'no_show_auto_forfeit_deposit' => ['sometimes', 'boolean'],
        ];
    }
}
