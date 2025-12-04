<?php

namespace App\Domains\ContentManagement\Tags\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TagsPermissionsSeeder extends Seeder
{
    /**
     * Seed the tags management permissions.
     *
     * This seeder creates permissions for managing post tags,
     * following the space-separated naming convention.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions for tag management
        $permissions = [
            // Tag CRUD
            'view tags',           // List and view tags
            'create tags',         // Create new tags
            'update tags',         // Edit existing tags
            'delete tags',         // Delete tags

            // Tag Operations
            'view popular tags',   // View popular tags list
            'search tags',         // Search tags
            'bulk manage tags',    // Bulk operations (merge, delete, etc.)
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

        // Admin gets full tag management
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        // Editor can manage tags
        if ($editor) {
            $editor->givePermissionTo([
                'view tags',
                'create tags',
                'update tags',
                'view popular tags',
                'search tags',
            ]);
        }

        // Author can create and view tags
        if ($author) {
            $author->givePermissionTo([
                'view tags',
                'create tags',
                'view popular tags',
                'search tags',
            ]);
        }
    }
}
