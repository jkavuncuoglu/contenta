<?php

namespace App\Domains\Security\Authentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Domains$1$2;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
            ],
            'new_password' => [
                'required',
                'uncompromised',
                'confirmed',
                new StrongPassword(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.uncompromised' => 'The password you entered has been found in a data leak. Please choose a different password.',
            'password.confirmed' => 'The password confirmation does not match.'
        ];
    }
}
