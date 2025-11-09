<?php

namespace App\Domains\Security\Authentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $userId],
            'first_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'username' => ['sometimes', 'nullable', 'string', 'max:255', 'unique:users,username,' . $userId],
            'bio' => ['sometimes', 'nullable', 'string'],
            'avatar' => ['sometimes', 'nullable', 'string'],
            'timezone' => ['sometimes', 'nullable', 'string', 'max:100'],
            'language' => ['sometimes', 'nullable', 'string', 'max:10'],
            'preferences' => ['sometimes', 'array'],
            'social_links' => ['sometimes', 'array'],
        ];
    }
}
