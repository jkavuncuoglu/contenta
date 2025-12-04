# Permission Consolidation Plan

**Date:** 2025-12-04
**Purpose:** Consolidate duplicate and similar permissions into a single consistent format

---

## Duplicate Analysis

### Format Duplicates (Space-separated vs Dot-notation)

These are OLD (dot-notation) vs NEW (space-separated) versions of the same permission:

| Old Format (Dot) | New Format (Space) | Action |
|------------------|-------------------|--------|
| `access.admin` | `access admin` | **Keep:** `access admin` |
| `dashboard.view` | `view dashboard` | **Keep:** `view dashboard` |
| `analytics.view` | `view analytics` | **Keep:** `view analytics` |
| `api-tokens.manage` | `manage api tokens` | **Keep:** `manage api tokens` |
| `api-tokens.use` | `use api` | **Keep:** `use api` |
| `backups.view/create/delete/restore` | `view/create/delete/restore backups` | **Keep:** space-separated |
| `categories.*` | `view/create/update/delete categories`, `view category tree`, `reorder categories` | **Keep:** space-separated |
| `comments.*` | `view/create/update/delete/moderate comments`, `approve comments`, `spam comments` | **Keep:** space-separated |
| `media.*` | `view/upload/update/delete media` | **Keep:** space-separated |
| `pages.*` | `view/create/update/delete/publish pages` | **Keep:** space-separated |
| `permissions.view` | `view permissions` | **Keep:** `view permissions` |
| `plugins.*` | `view/install/activate/deactivate/update/delete plugins`, `configure plugins` | **Keep:** space-separated |
| `post-types.*` | `view/create/update/delete/manage post types` | **Keep:** space-separated |
| `posts.*` | `view/create/update/delete/publish/unpublish/duplicate/moderate posts` | **Keep:** space-separated |
| `roles.*` | `view/create/update/delete roles` | **Keep:** space-separated |
| `security.*` | `view security logs`, `manage ip restrictions`, `test captcha`, `view audit log`, `clear audit log` | **Keep:** space-separated |
| `settings.*` | `view/update settings`, `view system settings`, `view page options`, `reset/export/import settings` | **Keep:** space-separated |
| `tags.*` | `view/create/update/delete tags`, `view popular tags`, `search tags`, `bulk manage tags` | **Keep:** space-separated |
| `themes.*` | `view/install/activate/customize/delete themes`, `preview themes` | **Keep:** space-separated |
| `users.*` | `view/create/update/delete users`, `manage user roles` | **Keep:** space-separated |

### Semantic Duplicates (Similar meanings)

| Duplicate Set | Recommended | Reason |
|---------------|-------------|--------|
| `manage api tokens`<br>`create api tokens`<br>`update api tokens`<br>`delete api tokens` | **Keep ALL** | Granular permissions allow fine-grained access control |
| `view media`<br>`view media library` | **Keep:** `view media` | "view media library" is redundant |
| `view post calendar`<br>`view calendar` | **Keep:** `view calendar` | Generic calendar covers all types |
| `moderate posts`<br>`moderate comments` | **Keep BOTH** | Different resources |
| `approve comments`<br>`spam comments`<br>`moderate comments` | **Keep ALL** | Different actions on comments |
| `update settings`<br>`update site settings`<br>`update security settings`<br>`update theme settings`<br>`update content storage settings` | **Keep ALL** | Different settings sections |
| `view settings`<br>`view system settings`<br>`view page options` | **Consolidate** to:<br>`view settings` (general)<br>`view security settings`<br>`view theme settings`<br>`view site settings` | Align with update permissions |

---

## Consolidated Permission List

### Core Admin
- `access admin` - Access admin panel

### Dashboard
- `view dashboard` - View admin dashboard

### Users
- `view users` - View users list
- `create users` - Create new users
- `update users` - Edit existing users
- `delete users` - Delete users
- `manage user roles` - Assign roles to users
- `impersonate users` - Impersonate other users
- `view user profiles` - View user profile details

### Posts
- `view posts` - View posts list
- `create posts` - Create new posts
- `update posts` - Edit existing posts
- `delete posts` - Delete posts
- `publish posts` - Publish posts
- `unpublish posts` - Unpublish posts
- `duplicate posts` - Duplicate existing posts
- `moderate posts` - Moderate post content

### Pages
- `view pages` - View pages list
- `create pages` - Create new pages
- `update pages` - Edit existing pages
- `delete pages` - Delete pages
- `publish pages` - Publish pages
- `manage page templates` - Manage page templates

### Media
- `view media` - View media library
- `upload media` - Upload new media
- `update media` - Edit media metadata
- `delete media` - Delete media
- `organize media` - Organize media into folders

### Categories
- `view categories` - View categories list
- `create categories` - Create new categories
- `update categories` - Edit existing categories
- `delete categories` - Delete categories
- `view category tree` - View category hierarchy
- `reorder categories` - Reorder categories

### Tags
- `view tags` - View tags list
- `create tags` - Create new tags
- `update tags` - Edit existing tags
- `delete tags` - Delete tags
- `view popular tags` - View popular tags
- `search tags` - Search tags
- `bulk manage tags` - Bulk tag operations

### Comments
- `view comments` - View comments list
- `create comments` - Create new comments
- `update comments` - Edit existing comments
- `delete comments` - Delete comments
- `moderate comments` - Moderate comments
- `approve comments` - Approve pending comments
- `spam comments` - Mark comments as spam

### Post Types
- `view post types` - View post types
- `create post types` - Create new post types
- `update post types` - Edit existing post types
- `delete post types` - Delete post types
- `manage post types` - Manage post type settings

### Roles & Permissions
- `view roles` - View roles list
- `create roles` - Create new roles
- `update roles` - Edit existing roles
- `delete roles` - Delete roles
- `view permissions` - View permissions list
- `assign permissions` - Assign permissions to roles

### Settings
- `view settings` - View general settings
- `view site settings` - View site settings section
- `view security settings` - View security settings section
- `view theme settings` - View theme settings section
- `update site settings` - Update site settings
- `update security settings` - Update security settings
- `update theme settings` - Update theme settings
- `reset settings` - Reset settings to default
- `export settings` - Export settings
- `import settings` - Import settings

### Plugins
- `view plugins` - View plugins list
- `install plugins` - Install new plugins
- `activate plugins` - Activate plugins
- `deactivate plugins` - Deactivate plugins
- `update plugins` - Update plugins
- `delete plugins` - Delete plugins
- `configure plugins` - Configure plugin settings

### Themes
- `view themes` - View themes list
- `install themes` - Install new themes
- `activate themes` - Activate themes
- `customize themes` - Customize theme appearance
- `delete themes` - Delete themes
- `preview themes` - Preview themes before activation

### Calendar
- `view calendar` - View unified calendar
- `filter calendar` - Use calendar filters

### Analytics
- `view analytics` - View analytics dashboard

### Backups
- `view backups` - View backups list
- `create backups` - Create new backups
- `restore backups` - Restore from backups
- `delete backups` - Delete backups

### Security
- `view security logs` - View security logs
- `manage ip restrictions` - Manage IP restrictions
- `test captcha` - Test CAPTCHA functionality
- `view audit log` - View audit log
- `clear audit log` - Clear audit log

### API Tokens
- `use api` - Use API endpoints
- `view api tokens` - View API tokens list
- `create api tokens` - Create new API tokens
- `update api tokens` - Edit existing API tokens
- `delete api tokens` - Delete API tokens

### Social Media
- `view social accounts` - View social media accounts
- `connect social accounts` - Connect social accounts
- `edit social accounts` - Edit social account settings
- `disconnect social accounts` - Disconnect social accounts
- `refresh social tokens` - Refresh social media tokens
- `view social posts` - View social media posts
- `create social posts` - Create social posts
- `edit social posts` - Edit social posts
- `delete social posts` - Delete social posts
- `publish social posts` - Publish social posts
- `view social analytics` - View social analytics
- `sync social analytics` - Sync social analytics

### Navigation/Menus
- `view menus` - View menus list
- `create menus` - Create new menus
- `update menus` - Edit existing menus
- `delete menus` - Delete menus
- `view menu items` - View menu items
- `create menu items` - Create new menu items
- `update menu items` - Edit existing menu items
- `delete menu items` - Delete menu items
- `reorder menu items` - Reorder menu items

### Content Storage
- `view content storage` - View content storage settings
- `update content storage settings` - Update storage settings
- `migrate content storage` - Migrate content between storage
- `view content migrations` - View migration history
- `verify content migrations` - Verify migration integrity
- `rollback content migrations` - Rollback migrations

---

## Permissions to Remove (OLD dot-notation format)

These are legacy permissions that should be deleted:

- `access.admin`
- `dashboard.view`
- `analytics.view`
- `api-tokens.manage`
- `api-tokens.use`
- `backups.view`
- `backups.create`
- `backups.restore`
- `backups.delete`
- `categories.view`
- `categories.create`
- `categories.update`
- `categories.delete`
- `categories.tree`
- `categories.reorder`
- `comments.view`
- `comments.create`
- `comments.update`
- `comments.delete`
- `comments.moderate`
- `media.view`
- `media.upload`
- `media.update`
- `media.delete`
- `pages.view`
- `pages.create`
- `pages.update`
- `pages.delete`
- `pages.publish`
- `permissions.view`
- `plugins.view`
- `plugins.install`
- `plugins.activate`
- `plugins.deactivate`
- `plugins.update`
- `plugins.delete`
- `post-types.view`
- `post-types.create`
- `post-types.update`
- `post-types.delete`
- `post-types.manage`
- `posts.view`
- `posts.create`
- `posts.update`
- `posts.delete`
- `posts.publish`
- `posts.unpublish`
- `posts.duplicate`
- `posts.moderate`
- `roles.view`
- `roles.create`
- `roles.update`
- `roles.delete`
- `security.view-logs`
- `security.manage-ip-restrictions`
- `security.test-captcha`
- `security.audit-log`
- `security.clear-audit-log`
- `security.logs`
- `settings.view`
- `settings.update`
- `settings.system`
- `settings.page-options`
- `settings.system.reset`
- `settings.system.export`
- `settings.system.import`
- `tags.view`
- `tags.create`
- `tags.update`
- `tags.delete`
- `tags.popular`
- `tags.search`
- `tags.bulk`
- `themes.view`
- `themes.install`
- `themes.activate`
- `themes.customize`
- `themes.delete`
- `users.view`
- `users.create`
- `users.update`
- `users.delete`
- `users.manage-roles`

**Permissions to Remove (Semantic duplicates):**
- `view media library` (use `view media` instead)
- `view post calendar` (use `view calendar` instead)
- `view system settings` (use `view site settings` instead)
- `view page options` (move to `view site settings`)
- `manage api tokens` (granular create/update/delete exists)
- `update settings` (use specific: update site/security/theme settings)

---

## Total Count

- **Current:** 212 web permissions
- **After cleanup:** ~105 consolidated permissions
- **Removed:** ~107 duplicate/legacy permissions

---

## Next Steps

1. Update RolesAndPermissionsSeeder to remove old dot-notation permissions
2. Find all permission checks in codebase
3. Replace old permission names with new ones
4. Create migration to:
   - Delete old permissions from database
   - Update any role_has_permissions entries to use new permissions
5. Test all permission checks work correctly
