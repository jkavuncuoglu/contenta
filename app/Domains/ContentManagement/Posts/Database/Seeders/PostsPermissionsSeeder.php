<?php

namespace App\Domains\ContentManagement\Posts\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PostsPermissionsSeeder extends Seeder
{
    /**
     * Seed the posts management permissions.
     *
     * This seeder creates permissions for managing blog posts,
     * following the space-separated naming convention.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions for post management
        $permissions = [
            // Post CRUD
            'view posts',        // List and view posts
            'create posts',      // Create new posts
            'update posts',      // Edit existing posts
            'delete posts',      // Delete posts

            // Publishing
            'publish posts',     // Publish posts
            'unpublish posts',   // Unpublish posts

            // Operations
            'duplicate posts',   // Duplicate posts

            // Moderation
            'moderate posts',    // Moderate post content (for spam, inappropriate content, etc.)

            // Legacy Calendar (maintain for backward compatibility)
            'view post calendar', // View posts calendar
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

        // Super-admin gets everything
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        // Admin gets full post management
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        // Editor can manage and publish posts
        if ($editor) {
            $editor->givePermissionTo([
                'view posts',
                'create posts',
                'update posts',
                'delete posts',
                'publish posts',
                'unpublish posts',
                'duplicate posts',
                'view post calendar',
            ]);
        }

        // Author can create, edit own, and duplicate
        if ($author) {
            $author->givePermissionTo([
                'view posts',
                'create posts',
                'update posts',
                'delete posts',
                'duplicate posts',
                'view post calendar',
            ]);
        }

        // Contributor can create and view only
        if ($contributor) {
            $contributor->givePermissionTo([
                'view posts',
                'create posts',
            ]);
        }
    }
}
