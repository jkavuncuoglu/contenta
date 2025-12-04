<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Core admin access
            'access admin',         // OLD: 'access.admin'

            // Dashboard
            'view dashboard',       // OLD: 'dashboard.view'

            // Users
            'view users', 'create users', 'update users', 'delete users', 'manage user roles',
            // OLD: 'users.view', 'users.create', 'users.update', 'users.delete', 'users.manage-roles'

            // Posts
            'view posts', 'create posts', 'update posts', 'delete posts', 'publish posts',
            'unpublish posts', 'duplicate posts', 'moderate posts',
            // OLD: 'posts.view', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish',
            // 'posts.unpublish', 'posts.duplicate', 'posts.moderate'

            // Pages
            'view pages', 'create pages', 'update pages', 'delete pages', 'publish pages',
            // OLD: 'pages.view', 'pages.create', 'pages.update', 'pages.delete', 'pages.publish'

            // Media
            'view media', 'upload media', 'update media', 'delete media',
            // OLD: 'media.view', 'media.upload', 'media.update', 'media.delete'

            // Categories
            'view categories', 'create categories', 'update categories', 'delete categories',
            'view category tree', 'reorder categories',
            // OLD: 'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            // 'categories.tree', 'categories.reorder'

            // Tags
            'view tags', 'create tags', 'update tags', 'delete tags',
            'view popular tags', 'search tags', 'bulk manage tags',
            // OLD: 'tags.view', 'tags.create', 'tags.update', 'tags.delete',
            // 'tags.popular', 'tags.search', 'tags.bulk'

            // Comments
            'view comments', 'create comments', 'update comments', 'delete comments', 'moderate comments',
            // OLD: 'comments.view', 'comments.create', 'comments.update', 'comments.delete', 'comments.moderate'

            // Post Types
            'view post types', 'create post types', 'update post types', 'delete post types', 'manage post types',
            // OLD: 'post-types.view', 'post-types.create', 'post-types.update', 'post-types.delete', 'post-types.manage'

            // Roles & Permissions
            'view roles', 'create roles', 'update roles', 'delete roles', 'view permissions', 'assign permissions',
            // OLD: 'roles.view', 'roles.create', 'roles.update', 'roles.delete', 'permissions.view'

            // Settings
            'view settings', 'update settings', 'view system settings', 'view page options',
            'reset settings', 'export settings', 'import settings',
            // OLD: 'settings.view', 'settings.update', 'settings.system', 'settings.page-options',
            // 'settings.system.reset', 'settings.system.export', 'settings.system.import'

            // Plugins
            'view plugins', 'install plugins', 'activate plugins', 'deactivate plugins',
            'update plugins', 'delete plugins',
            // OLD: 'plugins.view', 'plugins.install', 'plugins.activate', 'plugins.deactivate',
            // 'plugins.update', 'plugins.delete'

            // Themes
            'view themes', 'install themes', 'activate themes', 'customize themes', 'delete themes',
            // OLD: 'themes.view', 'themes.install', 'themes.activate', 'themes.customize', 'themes.delete'

            // Analytics
            'view analytics',       // OLD: 'analytics.view'

            // Backups
            'view backups', 'create backups', 'restore backups', 'delete backups',
            // OLD: 'backups.view', 'backups.create', 'backups.restore', 'backups.delete'

            // Security
            'view security logs', 'manage ip restrictions', 'test captcha',
            'view audit log', 'clear audit log',
            // OLD: 'security.view-logs', 'security.manage-ip-restrictions', 'security.test-captcha',
            // 'security.audit-log', 'security.clear-audit-log', 'security.logs'

            // API Tokens
            'use api', 'manage api tokens',
            // OLD: 'api-tokens.use', 'api-tokens.manage'
        ];

        // Create NEW space-separated permissions for BOTH guards
        foreach (['web', 'api'] as $guard) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard,
                ]);
            }
        }

        // BACKWARD COMPATIBILITY: Keep old dot-notation permissions temporarily (web guard only)
        $oldPermissions = [
            'access.admin', 'dashboard.view',
            'users.view', 'users.create', 'users.update', 'users.delete', 'users.manage-roles',
            'posts.view', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish',
            'posts.unpublish', 'posts.duplicate', 'posts.moderate',
            'pages.view', 'pages.create', 'pages.update', 'pages.delete', 'pages.publish',
            'media.view', 'media.upload', 'media.update', 'media.delete',
            'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'categories.tree', 'categories.reorder',
            'tags.view', 'tags.create', 'tags.update', 'tags.delete',
            'tags.popular', 'tags.search', 'tags.bulk',
            'comments.view', 'comments.create', 'comments.update', 'comments.delete', 'comments.moderate',
            'post-types.view', 'post-types.create', 'post-types.update', 'post-types.delete', 'post-types.manage',
            'roles.view', 'roles.create', 'roles.update', 'roles.delete', 'permissions.view',
            'settings.view', 'settings.update', 'settings.system', 'settings.page-options',
            'settings.system.reset', 'settings.system.export', 'settings.system.import',
            'plugins.view', 'plugins.install', 'plugins.activate', 'plugins.deactivate',
            'plugins.update', 'plugins.delete',
            'themes.view', 'themes.install', 'themes.activate', 'themes.customize', 'themes.delete',
            'analytics.view',
            'backups.view', 'backups.create', 'backups.restore', 'backups.delete',
            'security.view-logs', 'security.manage-ip-restrictions', 'security.test-captcha',
            'security.audit-log', 'security.clear-audit-log', 'security.logs',
            'api-tokens.use', 'api-tokens.manage',
        ];

        foreach ($oldPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create roles idempotently for BOTH guards
        $superAdminWeb = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $adminWeb = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editorWeb = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $authorWeb = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);
        $contributorWeb = Role::firstOrCreate(['name' => 'contributor', 'guard_name' => 'web']);
        $subscriberWeb = Role::firstOrCreate(['name' => 'subscriber', 'guard_name' => 'web']);

        $superAdminApi = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'api']);
        $adminApi = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $editorApi = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'api']);
        $authorApi = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'api']);
        $contributorApi = Role::firstOrCreate(['name' => 'contributor', 'guard_name' => 'api']);
        $subscriberApi = Role::firstOrCreate(['name' => 'subscriber', 'guard_name' => 'api']);

        // Super-admin gets EVERYTHING (for both guards)
        $superAdminWeb->syncPermissions(Permission::where('guard_name', 'web')->get());
        $superAdminApi->syncPermissions(Permission::where('guard_name', 'api')->get());

        // Admin gets NEW space-separated permissions (full access except super-admin actions)
        $adminWeb->syncPermissions([
            'access admin',
            'view dashboard',
            'view posts', 'create posts', 'update posts', 'delete posts', 'publish posts',
            'unpublish posts', 'duplicate posts', 'moderate posts',
            'view pages', 'create pages', 'update pages', 'delete pages', 'publish pages',
            'view media', 'upload media', 'update media', 'delete media',
            'view categories', 'create categories', 'update categories', 'delete categories',
            'view category tree', 'reorder categories',
            'view tags', 'create tags', 'update tags', 'delete tags',
            'view popular tags', 'search tags', 'bulk manage tags',
            'view comments', 'create comments', 'update comments', 'delete comments', 'moderate comments',
            'view users', 'create users', 'update users',
            'view analytics',
            'view settings', 'view page options',
            'manage post types', 'view post types', 'create post types', 'update post types', 'delete post types',
            'use api', 'manage api tokens',
            'view roles', 'create roles', 'update roles', 'delete roles', 'view permissions',
        ]);

        $adminApi->syncPermissions([
            'access admin',
            'view dashboard',
            'view posts', 'create posts', 'update posts', 'delete posts', 'publish posts',
            'unpublish posts', 'duplicate posts', 'moderate posts',
            'view pages', 'create pages', 'update pages', 'delete pages', 'publish pages',
            'view media', 'upload media', 'update media', 'delete media',
            'view categories', 'create categories', 'update categories', 'delete categories',
            'view category tree', 'reorder categories',
            'view tags', 'create tags', 'update tags', 'delete tags',
            'view popular tags', 'search tags', 'bulk manage tags',
            'view comments', 'create comments', 'update comments', 'delete comments', 'moderate comments',
            'view users', 'create users', 'update users',
            'view analytics',
            'view settings', 'view page options',
            'manage post types', 'view post types', 'create post types', 'update post types', 'delete post types',
            'use api', 'manage api tokens',
            'view roles', 'create roles', 'update roles', 'delete roles', 'view permissions',
        ]);

        // Editor permissions (NEW format)
        $editorWeb->syncPermissions([
            'access admin',
            'view dashboard',
            'view posts', 'create posts', 'update posts', 'delete posts', 'publish posts',
            'unpublish posts', 'duplicate posts',
            'view pages', 'create pages', 'update pages', 'delete pages', 'publish pages',
            'view media', 'upload media', 'update media', 'delete media',
            'view categories', 'create categories', 'update categories', 'view category tree',
            'view tags', 'create tags', 'update tags', 'view popular tags', 'search tags',
            'view comments', 'moderate comments',
            'view analytics',
            'use api',
        ]);

        $editorApi->syncPermissions([
            'access admin',
            'view dashboard',
            'view posts', 'create posts', 'update posts', 'delete posts', 'publish posts',
            'unpublish posts', 'duplicate posts',
            'view pages', 'create pages', 'update pages', 'delete pages', 'publish pages',
            'view media', 'upload media', 'update media', 'delete media',
            'view categories', 'create categories', 'update categories', 'view category tree',
            'view tags', 'create tags', 'update tags', 'view popular tags', 'search tags',
            'view comments', 'moderate comments',
            'view analytics',
            'use api',
        ]);

        // Author permissions (NEW format)
        $authorWeb->syncPermissions([
            'access admin',
            'view dashboard',
            'view posts', 'create posts', 'update posts', 'delete posts', 'duplicate posts',
            'view media', 'upload media',
            'view categories', 'view category tree',
            'view tags', 'create tags', 'view popular tags', 'search tags',
            'view comments',
            'use api',
        ]);

        $authorApi->syncPermissions([
            'access admin',
            'view dashboard',
            'view posts', 'create posts', 'update posts', 'delete posts', 'duplicate posts',
            'view media', 'upload media',
            'view categories', 'view category tree',
            'view tags', 'create tags', 'view popular tags', 'search tags',
            'view comments',
            'use api',
        ]);

        $contributorWeb->syncPermissions([
            'view posts', 'create posts',
            'view media',
            'view categories',
            'view tags',
            'view comments',
        ]);

        $subscriberWeb->syncPermissions([
            'view posts',
            'view pages',
            'view comments',
            'create comments',
        ]);


    }
}
