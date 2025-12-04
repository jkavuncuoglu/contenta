<?php

namespace App\Domains\Security\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserManagementPermissionsSeeder extends Seeder
{
    /**
     * Seed the user management permissions.
     *
     * This seeder creates permissions for managing users in the system,
     * following the space-separated naming convention.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions for user management
        $permissions = [
            // User CRUD
            'view users',        // List and view users
            'create users',      // Create new users
            'update users',      // Edit existing users
            'delete users',      // Delete users

            // User Role Management
            'manage user roles', // Assign/revoke roles to/from users

            // User Profile
            'view user profiles', // View detailed user profiles

            // Advanced Features
            'impersonate users',  // Impersonate other users (admin debugging)
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

        // Super-admin gets everything
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        // Admin gets user management but not impersonation (security best practice)
        if ($admin) {
            $admin->givePermissionTo([
                'view users',
                'create users',
                'update users',
                // Deliberately excluding 'delete users' for safety
                'manage user roles',
                'view user profiles',
                // Deliberately excluding 'impersonate users' - super-admin only
            ]);
        }
    }
}
