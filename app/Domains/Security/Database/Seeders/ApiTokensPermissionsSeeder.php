<?php

namespace App\Domains\Security\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ApiTokensPermissionsSeeder extends Seeder
{
    /**
     * Seed the API tokens management permissions.
     *
     * This seeder creates permissions for managing API tokens (Laravel Sanctum),
     * following the space-separated naming convention.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions for API token management
        $permissions = [
            // API Token CRUD
            'view api tokens',   // List and view API tokens
            'create api tokens', // Create new API tokens
            'update api tokens', // Edit existing API tokens (e.g., rename, change abilities)
            'delete api tokens', // Revoke/delete API tokens

            // API Usage
            'use api',           // Use the API with tokens (base permission for API access)
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
        $editor = Role::where('name', 'editor')->where('guard_name', 'web')->first();
        $author = Role::where('name', 'author')->where('guard_name', 'web')->first();
        $contributor = Role::where('name', 'contributor')->where('guard_name', 'web')->first();
        $subscriber = Role::where('name', 'subscriber')->where('guard_name', 'web')->first();

        // Super-admin gets everything
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        // Admin gets full API token management
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        // Editor, Author, Contributor, Subscriber get API usage but manage their own tokens
        $limitedRoles = [$editor, $author, $contributor, $subscriber];
        foreach ($limitedRoles as $role) {
            if ($role) {
                $role->givePermissionTo([
                    'view api tokens',    // View own tokens
                    'create api tokens',  // Create own tokens
                    'delete api tokens',  // Delete own tokens
                    'use api',            // Use the API
                ]);
            }
        }
    }
}
