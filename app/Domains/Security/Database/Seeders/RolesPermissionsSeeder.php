<?php

namespace App\Domains\Security\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Seed the roles and permissions management permissions.
     *
     * This seeder creates permissions for managing roles and permissions themselves,
     * following the space-separated naming convention.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions for roles and permissions management
        $permissions = [
            // Role Management
            'view roles',        // List and view roles
            'create roles',      // Create new roles
            'update roles',      // Edit existing roles
            'delete roles',      // Delete roles

            // Permission Management
            'view permissions',  // List and view permissions
            'assign permissions', // Assign permissions to roles

            // User Role Assignment
            'manage user roles', // Assign/revoke roles to/from users
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Assign to roles
        $superAdmin = Role::where('name', 'super-admin')->where('guard_name', 'web')->first();
        $admin = Role::where('name', 'admin')->where('guard_name', 'web')->first();

        // Super-admin gets everything (already has all, but ensure these specific ones)
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        // Admin gets all role/permission management except deleting roles (conservative approach)
        if ($admin) {
            $admin->givePermissionTo([
                'view roles',
                'create roles',
                'update roles',
                // Deliberately excluding 'delete roles' for safety
                'view permissions',
                'assign permissions',
                'manage user roles',
            ]);
        }
    }
}
