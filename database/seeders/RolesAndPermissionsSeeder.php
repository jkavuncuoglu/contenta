<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Core admin access
            'access.admin',

            // Dashboard
            'dashboard.view',

            // Users
            'users.view', 'users.create', 'users.update', 'users.delete', 'users.manage-roles',

            // Posts
            'posts.view', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish', 'posts.unpublish', 'posts.duplicate', 'posts.moderate',

            // Pages
            'pages.view', 'pages.create', 'pages.update', 'pages.delete', 'pages.publish',

            // Media
            'media.view', 'media.upload', 'media.update', 'media.delete',

            // Categories
            'categories.view', 'categories.create', 'categories.update', 'categories.delete', 'categories.tree', 'categories.reorder',

            // Tags
            'tags.view', 'tags.create', 'tags.update', 'tags.delete', 'tags.popular', 'tags.search', 'tags.bulk',

            // Comments
            'comments.view', 'comments.create', 'comments.update', 'comments.delete', 'comments.moderate',

            // Post Types
            'post-types.view', 'post-types.create', 'post-types.update', 'post-types.delete', 'post-types.manage',

            // Roles & Permissions
            'roles.view', 'roles.create', 'roles.update', 'roles.delete', 'permissions.view',

            // Settings
            'settings.view', 'settings.update', 'settings.system', 'settings.page-options', 'settings.system.reset', 'settings.system.export', 'settings.system.import',

            // Plugins
            'plugins.view', 'plugins.install', 'plugins.activate', 'plugins.deactivate', 'plugins.update', 'plugins.delete',

            // Themes
            'themes.view', 'themes.install', 'themes.activate', 'themes.customize', 'themes.delete',

            // Analytics
            'analytics.view',

            // Backups
            'backups.view', 'backups.create', 'backups.restore', 'backups.delete',

            // Security
            'security.view-logs', 'security.manage-ip-restrictions', 'security.test-captcha', 'security.audit-log', 'security.clear-audit-log', 'security.logs',
        ];

        // Create permissions idempotently with explicit guard
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create roles idempotently with explicit guard
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin      = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editor     = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $author     = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);
        $contrib    = Role::firstOrCreate(['name' => 'contributor', 'guard_name' => 'web']);
        $subscriber = Role::firstOrCreate(['name' => 'subscriber', 'guard_name' => 'web']);

        // Assign permissions
        $superAdmin->syncPermissions(Permission::where('guard_name', 'web')->get());

        $admin->syncPermissions([
            // Access + content and management
            'access.admin',
            'dashboard.view',
            'posts.view','posts.create','posts.update','posts.delete','posts.publish','posts.unpublish','posts.duplicate','posts.moderate',
            'pages.view','pages.create','pages.update','pages.delete','pages.publish',
            'media.view','media.upload','media.update','media.delete',
            'categories.view','categories.create','categories.update','categories.delete','categories.tree','categories.reorder',
            'tags.view','tags.create','tags.update','tags.delete','tags.popular','tags.search','tags.bulk',
            'comments.view','comments.create','comments.update','comments.delete','comments.moderate',
            'users.view','users.create','users.update',
            'analytics.view',
            'settings.view','settings.page-options',
            // Post types management reserved for admins+
            'post-types.manage',
            'post-types.view','post-types.create','post-types.update','post-types.delete',
        ]);

        $editor->syncPermissions([
            'access.admin',
            'dashboard.view',
            'posts.view','posts.create','posts.update','posts.delete','posts.publish','posts.unpublish','posts.duplicate',
            'pages.view','pages.create','pages.update','pages.delete','pages.publish',
            'media.view','media.upload','media.update','media.delete',
            'categories.view','categories.create','categories.update','categories.tree',
            'tags.view','tags.create','tags.update','tags.popular','tags.search',
            'comments.view','comments.moderate',
            'analytics.view',
        ]);

        $author->syncPermissions([
            'access.admin',
            'dashboard.view',
            'posts.view','posts.create','posts.update','posts.delete','posts.duplicate',
            'media.view','media.upload',
            'categories.view','categories.tree',
            'tags.view','tags.create','tags.popular','tags.search',
            'comments.view',
        ]);

        $contrib->syncPermissions([
            'posts.view','posts.create',
            'media.view',
            'categories.view',
            'tags.view',
            'comments.view',
        ]);

        $subscriber->syncPermissions([
            'posts.view',
            'pages.view',
            'comments.view','comments.create',
        ]);
    }
}
