<?php

namespace App\Domains\ContentManagement\Categories\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CategoriesPermissionsSeeder extends Seeder
{
    /**
     * Seed the categories management permissions.
     *
     * This seeder creates permissions for managing post categories,
     * following the space-separated naming convention.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions for category management
        $permissions = [
            // Category CRUD
            'view categories',      // List and view categories
            'create categories',    // Create new categories
            'update categories',    // Edit existing categories
            'delete categories',    // Delete categories

            // Tree Operations
            'view category tree',   // View category hierarchy/tree structure
            'reorder categories',   // Reorder categories in tree
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

        // Super-admin gets everything
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        // Admin gets full category management
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        // Editor can manage categories
        if ($editor) {
            $editor->givePermissionTo([
                'view categories',
                'create categories',
                'update categories',
                'view category tree',
            ]);
        }

        // Author can view only
        if ($author) {
            $author->givePermissionTo([
                'view categories',
                'view category tree',
            ]);
        }
    }
}
