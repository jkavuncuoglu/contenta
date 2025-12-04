<?php

namespace App\Domains\ContentManagement\Pages\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PagesPermissionsSeeder extends Seeder
{
    /**
     * Seed the pages management permissions.
     *
     * This seeder creates permissions for managing static pages,
     * following the space-separated naming convention.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions for page management
        $permissions = [
            // Page CRUD
            'view pages',          // List and view pages
            'create pages',        // Create new pages
            'update pages',        // Edit existing pages
            'delete pages',        // Delete pages

            // Publishing
            'publish pages',       // Publish pages

            // Templates
            'manage page templates', // Manage page templates and layouts
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

        // Admin gets full page management
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        // Editor can manage pages
        if ($editor) {
            $editor->givePermissionTo([
                'view pages',
                'create pages',
                'update pages',
                'delete pages',
                'publish pages',
            ]);
        }
    }
}
