<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by controller policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_active' => ['sometimes', 'boolean'],
            'auto_post_enabled' => ['sometimes', 'boolean'],
            'auto_post_mode' => ['sometimes', 'string', 'in:immediate,scheduled'],
            'scheduled_post_time' => ['nullable', 'date_format:H:i:s', 'required_if:auto_post_mode,scheduled'],
            'platform_settings' => ['sometimes', 'array'],
            'platform_settings.*.type' => ['sometimes', 'string'],
            'platform_settings.*.label' => ['sometimes', 'string'],
            'platform_settings.*.value' => ['sometimes'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'auto_post_mode.in' => 'Auto-post mode must be either "immediate" or "scheduled".',
            'scheduled_post_time.required_if' => 'Scheduled time is required when auto-post mode is "scheduled".',
            'scheduled_post_time.date_format' => 'Scheduled time must be in HH:MM:SS format (e.g., 09:00:00).',
        ];
    }
}
