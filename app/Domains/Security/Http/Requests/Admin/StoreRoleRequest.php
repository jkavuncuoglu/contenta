<?php

namespace App\Domains\Security\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create roles');
    }

    /**
     * @return array<string, array<int, string|object>|string>
     */
    public function rules(): array
    {
        $guardName = $this->input('guard_name', 'web');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', $guardName),
            ],
            'guard_name' => ['sometimes', 'string', 'in:web,api'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')->where('guard_name', $guardName),
            ],
        ];
    }
}
