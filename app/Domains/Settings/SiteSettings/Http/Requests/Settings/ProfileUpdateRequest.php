<?php

namespace App\Domains\Settings\SiteSettings\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', function ($attribute, $value, $fail) {
                // If it's a file, validate it as an image
                if ($this->hasFile('avatar')) {
                    $file = $this->file('avatar');
                    if (! $file->isValid()) {
                        $fail('The avatar file is invalid.');
                    }
                    if (! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                        $fail('The avatar must be an image (jpeg, png, gif, webp).');
                    }
                    if ($file->getSize() > 5 * 1024 * 1024) { // 5MB
                        $fail('The avatar must not be larger than 5MB.');
                    }
                }
                // If it's a string, validate it as a URL
                elseif (is_string($value) && ! empty($value)) {
                    if (! filter_var($value, FILTER_VALIDATE_URL)) {
                        $fail('The avatar must be a valid URL.');
                    }
                }
            }],
            'timezone' => ['nullable', 'string', 'max:100'],
            'language' => ['nullable', 'string', 'max:10'],
            'preferences' => ['nullable', 'array'],
            'social_links' => ['nullable', 'array'],
        ];
    }
}
