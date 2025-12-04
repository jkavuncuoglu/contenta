<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SocialMediaPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define social media permissions
        $permissions = [
            // Account Management
            'view social accounts',
            'connect social accounts',
            'edit social accounts',
            'disconnect social accounts',
            'refresh social tokens',

            // Post Management
            'view social posts',
            'create social posts',
            'edit social posts',
            'delete social posts',
            'publish social posts',

            // Analytics
            'view social analytics',
            'sync social analytics',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create Social Media Manager role
        $socialMediaManager = Role::firstOrCreate([
            'name' => 'Social Media Manager',
            'guard_name' => 'web',
        ]);

        // Assign all social media permissions to the role
        $socialMediaManager->syncPermissions($permissions);

        // Optionally, grant all permissions to admin role if it exists
        $admin = Role::where('name', 'Admin')->where('guard_name', 'web')->first();
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        $this->command->info('Social Media permissions and roles created successfully!');
        $this->command->info('Created ' . count($permissions) . ' permissions');
        $this->command->info('Created/Updated "Social Media Manager" role');
    }
}
