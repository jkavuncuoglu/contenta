<?php

namespace App\Domains\Themes\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ThemesPermissionsSeeder extends Seeder
{
    /**
     * Seed the themes management permissions.
     *
     * This seeder creates permissions for managing themes,
     * following the space-separated naming convention.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions for theme management
        $permissions = [
            // Theme Management
            'view themes',         // List and view themes
            'install themes',      // Install new themes
            'activate themes',     // Activate themes
            'customize themes',    // Customize theme settings
            'delete themes',       // Delete/uninstall themes
            'preview themes',      // Preview themes before activation
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

        // Super-admin ONLY - themes can execute arbitrary code
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }
    }
}
