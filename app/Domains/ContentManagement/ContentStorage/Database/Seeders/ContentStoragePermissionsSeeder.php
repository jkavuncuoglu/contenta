<?php

namespace App\Domains\ContentManagement\ContentStorage\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ContentStoragePermissionsSeeder extends Seeder
{
    /**
     * Seed the content storage management permissions.
     *
     * This seeder creates permissions for managing content storage settings and migrations,
     * following the space-separated naming convention.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions for content storage management
        $permissions = [
            // Content Storage Settings
            'view content storage',           // View content storage settings
            'update content storage settings', // Update content storage configuration

            // Migration Operations
            'migrate content storage',         // Perform content storage migrations (database/file)
            'view content migrations',         // View migration history
            'verify content migrations',       // Verify migration integrity
            'rollback content migrations',     // Rollback migrations
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

        // Super-admin ONLY - content migrations are dangerous operations
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }
    }
}
