<?php

namespace App\Domains\Security\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Security\Http\Requests\Admin\StoreRoleRequest;
use App\Domains\Security\Http\Requests\Admin\UpdateRoleRequest;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\RedirectResponse;

class RolesController extends Controller
{
    public function index(): Response
    {
        $roles = Role::with(['permissions:id,name'])->withCount('users')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get(['id','name']);

        return Inertia::render('admin/settings/permissions/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return redirect()->route('settings.permissions.index')
            ->with('success', 'Role created successfully.');
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin' && $request->input('name') !== $role->name) {
            return back()->with('error', 'The super-admin role name cannot be changed.');
        }

        $data = $request->validated();
        $role->name = $data['name'];
        $role->save();

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('settings.permissions.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin') {
            return back()->with('error', 'The super-admin role cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('settings.permissions.index')
            ->with('success', 'Role deleted successfully.');
    }
}

