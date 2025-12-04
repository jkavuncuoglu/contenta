<?php

namespace App\Domains\Media\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MediaPermissionsSeeder extends Seeder
{
    /**
     * Seed the media management permissions.
     *
     * This seeder creates permissions for managing media library,
     * following the space-separated naming convention.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions for media management
        $permissions = [
            // Media CRUD
            'view media',        // List and view media
            'upload media',      // Upload new media files
            'update media',      // Edit media metadata
            'delete media',      // Delete media files

            // Media Organization
            'organize media',    // Organize media (folders, collections, etc.)
            'view media library', // Access media library interface
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

        // Admin gets full media management
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        // Editor can manage media
        if ($editor) {
            $editor->givePermissionTo([
                'view media',
                'upload media',
                'update media',
                'delete media',
                'view media library',
            ]);
        }

        // Author can upload and view media
        if ($author) {
            $author->givePermissionTo([
                'view media',
                'upload media',
                'view media library',
            ]);
        }
    }
}
