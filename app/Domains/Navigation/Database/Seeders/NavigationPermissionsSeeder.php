<?php

namespace App\Domains\Navigation\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NavigationPermissionsSeeder extends Seeder
{
    /**
     * Seed the navigation management permissions.
     *
     * This seeder creates permissions for managing menus and navigation,
     * following the space-separated naming convention.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions for navigation management
        $permissions = [
            // Menu Management
            'view menus',          // List and view menus
            'create menus',        // Create new menus
            'update menus',        // Edit existing menus
            'delete menus',        // Delete menus

            // Menu Item Management
            'view menu items',     // List and view menu items
            'create menu items',   // Create new menu items
            'update menu items',   // Edit existing menu items
            'delete menu items',   // Delete menu items
            'reorder menu items',  // Reorder menu items in hierarchy
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

        // Super-admin gets everything
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        // Admin gets full navigation management
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        // Editor can manage menu items but not menus
        if ($editor) {
            $editor->givePermissionTo([
                'view menus',
                'view menu items',
                'create menu items',
                'update menu items',
                'delete menu items',
                'reorder menu items',
            ]);
        }
    }
}
