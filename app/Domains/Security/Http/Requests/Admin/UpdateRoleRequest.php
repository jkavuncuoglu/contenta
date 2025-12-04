<?php

namespace App\Domains\Security\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update roles');
    }

    /**
     * @return array<string, array<int, string|object>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');
        $roleId = $role instanceof Role ? $role->id : $role;
        $guardName = $role->guard_name ?? 'web';

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->ignore($roleId)
                    ->where('guard_name', $guardName),
            ],
            'guard_name' => ['sometimes', 'string', 'in:web,api'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'integer',
//                Rule::exists('permissions', 'id')->where('guard_name', $guardName),
            ],
        ];
    }
}
