<?php

namespace App\Domains\Security\Http\Controllers\Admin;

use App\Domains\Security\Http\Requests\Admin\StoreRoleRequest;
use App\Domains\Security\Http\Requests\Admin\UpdateRoleRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function index(): Response
    {
        $roles = Role::with(['permissions:id,name,guard_name'])->withCount('users')->orderBy('name')->get();

        // Get all permissions grouped by guard for frontend filtering
        $permissions = Permission::orderBy('name')->get(['id', 'name', 'guard_name']);

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
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        if (! empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return redirect()->route('admin.settings.permissions.index')
            ->with('success', 'Role created successfully.');
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin' && $request->input('name') !== $role->name) {
            return back()->with('error', 'The super-admin role name cannot be changed.');
        }

        $data = $request->validated();
        $role->name = $data['name'];

        // Update guard_name if provided (and role is not super-admin)
        if (isset($data['guard_name']) && $role->name !== 'super-admin') {
            $role->guard_name = $data['guard_name'];
        }

        $role->save();

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.settings.permissions.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin') {
            return back()->with('error', 'The super-admin role cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('admin.settings.permissions.index')
            ->with('success', 'Role deleted successfully.');
    }
}
