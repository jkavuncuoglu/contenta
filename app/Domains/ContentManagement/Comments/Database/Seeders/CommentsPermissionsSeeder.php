<?php

namespace App\Domains\ContentManagement\Comments\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CommentsPermissionsSeeder extends Seeder
{
    /**
     * Seed the comments management permissions.
     *
     * This seeder creates permissions for managing post comments,
     * following the space-separated naming convention.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions for comment management
        $permissions = [
            // Comment CRUD
            'view comments',       // List and view comments
            'create comments',     // Create new comments
            'update comments',     // Edit existing comments
            'delete comments',     // Delete comments

            // Moderation
            'moderate comments',   // Moderate comments (approve, reject, mark spam)
            'approve comments',    // Approve pending comments
            'spam comments',       // Mark comments as spam
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
        $subscriber = Role::where('name', 'subscriber')->where('guard_name', 'web')->first();

        // Super-admin gets everything
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        // Admin gets full comment management
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        // Editor can moderate comments
        if ($editor) {
            $editor->givePermissionTo([
                'view comments',
                'moderate comments',
            ]);
        }

        // Author can view comments
        if ($author) {
            $author->givePermissionTo([
                'view comments',
            ]);
        }

        // Subscriber can view and create comments
        if ($subscriber) {
            $subscriber->givePermissionTo([
                'view comments',
                'create comments',
            ]);
        }
    }
}
