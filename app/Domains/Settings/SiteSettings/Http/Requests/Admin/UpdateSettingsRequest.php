<?php

namespace App\Domains\Settings\SiteSettings\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('manage_settings');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'blog' => 'sometimes|array',
            'blog.blog_slug' => 'required_with:blog|string|max:255|regex:/^[a-z0-9-]+$/',
            'blog.primary_landing_page' => 'required_with:blog|string|max:255',

            'analytics' => 'sometimes|array',
            'analytics.google_analytics_id' => 'nullable|string|max:255',
            'analytics.google_tag_manager_id' => 'nullable|string|max:255',

            'security' => 'sometimes|array',
            'security.recaptcha_enabled' => 'sometimes|boolean',
            'security.recaptcha_site_key' => 'required_if:security.recaptcha_enabled,true|nullable|string|max:255',
            'security.recaptcha_secret_key' => 'required_if:security.recaptcha_enabled,true|nullable|string|max:255',
            'security.honeypot_enabled' => 'sometimes|boolean',
            'security.honeypot_field_name' => 'required_with:security.honeypot_enabled|string|max:50',
            'security.honeypot_timer_field_name' => 'required_with:security.honeypot_enabled|string|max:50',
            'security.honeypot_minimum_time' => 'required_with:security.honeypot_enabled|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'blog.blog_slug.regex' => 'The blog slug may only contain lowercase letters, numbers, and hyphens.',
            'security.recaptcha_site_key.required_if' => 'The reCAPTCHA site key is required when reCAPTCHA is enabled.',
            'security.recaptcha_secret_key.required_if' => 'The reCAPTCHA secret key is required when reCAPTCHA is enabled.',
        ];
    }
}
