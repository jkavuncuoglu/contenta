<?php

namespace App\Domains\Security\ApiTokens\Http\Requests;

use App\Domains\Security\ApiTokens\Constants\TokenAbility;
use Illuminate\Foundation\Http\FormRequest;

class CreateApiTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['sometimes', 'array'],
            'abilities.*' => ['string', 'in:' . implode(',', TokenAbility::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Token name is required.',
            'name.max' => 'Token name cannot exceed 255 characters.',
            'abilities.*.in' => 'Invalid ability selected.',
        ];
    }
}

