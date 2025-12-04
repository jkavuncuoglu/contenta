<?php

namespace App\Domains\Plugins\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PluginsPermissionsSeeder extends Seeder
{
    /**
     * Seed the plugins management permissions.
     *
     * This seeder creates permissions for managing plugins,
     * following the space-separated naming convention.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions for plugin management
        $permissions = [
            // Plugin Management
            'view plugins',        // List and view plugins
            'install plugins',     // Install new plugins
            'activate plugins',    // Activate plugins
            'deactivate plugins',  // Deactivate plugins
            'update plugins',      // Update plugins to new versions
            'delete plugins',      // Delete/uninstall plugins
            'configure plugins',   // Configure plugin settings
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

        // Super-admin ONLY - plugins can execute arbitrary code
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }
    }
}
