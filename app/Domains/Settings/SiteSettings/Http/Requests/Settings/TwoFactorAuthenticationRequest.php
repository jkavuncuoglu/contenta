<?php

namespace App\Domains\Settings\SiteSettings\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorAuthenticationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'sometimes|required|string|size:6',
            'recovery_code' => 'sometimes|required|string',
        ];
    }

    /**
     * Ensure that the state is valid for the request.
     */
    public function ensureStateIsValid(): void
    {
        // Add any state validation logic here if needed
    }
}
