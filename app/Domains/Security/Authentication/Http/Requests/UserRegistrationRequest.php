<?php

namespace App\Domains\Security\Authentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Domains\Security\Authentication\Rules\StrongPassword;

class UserRegistrationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:user_emails,email'],
            'username' => ['sometimes', 'nullable', 'string', 'max:255', 'unique:users,username'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'avatar' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'timezone' => ['required', 'string', 'max:255', 'in:'.implode(',', timezone_identifiers_list())],
            'language' => ['required', 'string', 'max:5', 'regex:/^[a-z]{2}(-[A-Z]{2})?$/'],
            'password' => ['required', 'confirmed', new StrongPassword()],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'The email address is already registered.',
            'username.unique' => 'The username is already taken.',
            'password.confirmed' => 'The password confirmation does not match.',
            'language.regex' => 'The language format is invalid. Use format like "en" or "en-US".',
        ];
    }
}
