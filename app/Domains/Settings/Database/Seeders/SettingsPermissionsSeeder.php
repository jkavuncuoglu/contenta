<?php

namespace App\Domains\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SettingsPermissionsSeeder extends Seeder
{
    /**
     * Seed the settings management permissions.
     *
     * This seeder creates permissions for managing site settings, security settings,
     * theme settings, and system configuration, following the space-separated naming convention.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions for settings management
        $permissions = [
            // General Settings
            'view settings',         // View settings pages

            // Site Settings
            'update site settings',  // Update site name, description, SEO, etc.

            // Security Settings
            'update security settings', // Update security configuration (2FA, sessions, etc.)

            // Theme Settings
            'update theme settings', // Update theme configuration and customization

            // Page Options Settings
            'view page options',     // View page-specific settings

            // Content Storage Settings
            'view content storage',       // View content storage settings
            'update content storage settings', // Update content storage configuration
            'migrate content storage',    // Perform content migrations

            // System Operations
            'export settings',       // Export configuration
            'import settings',       // Import configuration
            'reset settings',        // Reset settings to defaults (dangerous)
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

        // Super-admin gets everything
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        // Admin gets most settings except dangerous operations
        if ($admin) {
            $admin->givePermissionTo([
                'view settings',
                'update site settings',
                'update security settings',
                'update theme settings',
                'view page options',
                'view content storage',
                'update content storage settings',
                // Deliberately excluding dangerous operations for safety:
                // - 'migrate content storage' (super-admin only)
                // - 'export settings' (super-admin only)
                // - 'import settings' (super-admin only)
                // - 'reset settings' (super-admin only)
            ]);
        }
    }
}
