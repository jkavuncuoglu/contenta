# Role Permission Edit Fix

**Date:** 2025-12-04
**Issue:** Role editing failing with validation errors for permissions and "name already taken"

---

## Problems Identified

### 1. Permission Validation Mismatch
**Problem:** Form requests were validating `permissions.*` as `exists:permissions,name`, but frontend was sending permission **IDs** (integers).

**Error:**
```json
{
  "permissions.0": "The selected permissions.0 is invalid.",
  "permissions.1": "The selected permissions.1 is invalid.",
  ...
}
```

**Root Cause:** Frontend sends: `permissions: [1, 2, 3, ...]` (IDs)
But validation expected: `permissions: ['view posts', 'create posts', ...]` (names)

### 2. Unique Name Validation on Edit
**Problem:** When editing a role without changing the name, unique validation failed.

**Error:**
```json
{
  "name": "The name has already been taken."
}
```

**Root Cause:** Unique validation wasn't excluding the current role ID.

### 3. Permission Grouping in Frontend
**Problem:** Vue component was grouping permissions by splitting on `.` (dot), which doesn't work with new space-separated permissions like "view posts".

**Impact:** Permissions like "view posts", "create posts" weren't being grouped correctly as "Posts" group.

---

## Fixes Applied

### Fix 1: Update Permission Validation (Backend)

**File:** `app/Domains/Security/Http/Requests/Admin/UpdateRoleRequest.php`

**Changed:**
```php
// OLD - Expected permission names (strings)
'permissions.*' => ['exists:permissions,name'],

// NEW - Expects permission IDs (integers) - matches frontend behavior
'permissions.*' => ['integer', 'exists:permissions,id'],
```

**File:** `app/Domains/Security/Http/Requests/Admin/StoreRoleRequest.php`

**Changed:** Same as above

### Fix 2: Fix Unique Validation for Role Name

**File:** `app/Domains/Security/Http/Requests/Admin/UpdateRoleRequest.php`

**Changed:**
```php
// OLD - Might fail to get ID
$roleId = $this->route('role')->id ?? null;

// NEW - Handles both model binding and raw ID
$role = $this->route('role');
$roleId = $role instanceof \Spatie\Permission\Models\Role ? $role->id : $role;

return [
    'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$roleId],
    // ...
];
```

This ensures the unique validation excludes the current role when editing.

### Fix 3: Update Permission Grouping (Frontend)

**File:** `resources/js/pages/admin/settings/permissions/Index.vue`

**Changed:**
```typescript
// OLD - Only handled dot-notation
const seg = p.name.split('.')[0] || 'general';

// NEW - Handles both formats
let seg: string;
if (p.name.includes('.')) {
    // Old format: "posts.view" -> "posts"
    seg = p.name.split('.')[0] || 'general';
} else {
    // New format: "view posts" -> "posts" (last word)
    const parts = p.name.split(' ');
    seg = parts.length > 1 ? parts[parts.length - 1] : 'general';
}
```

**Grouping Logic:**
- Old format `"posts.view"` → Group: **"Posts"**
- New format `"view posts"` → Group: **"Posts"**
- New format `"view calendar"` → Group: **"Calendar"**
- New format `"access admin"` → Group: **"Admin"**

### Fix 4: Remove Redundant Controller Middleware

**File:** `app/Domains/Security/Http/Controllers/Admin/RolesController.php`

**Removed:**
```php
public function __construct()
{
    $this->middleware('permission:view roles')->only(['index']);
    // ... etc
}
```

**Reason:** Routes already protected with middleware in `routes/admin/settings.php`. The constructor middleware was redundant and causing `Call to undefined method middleware()` error.

---

## Files Modified

1. `app/Domains/Security/Http/Requests/Admin/UpdateRoleRequest.php` - Fixed permission validation and unique name check
2. `app/Domains/Security/Http/Requests/Admin/StoreRoleRequest.php` - Fixed permission validation
3. `app/Domains/Security/Http/Controllers/Admin/RolesController.php` - Removed redundant middleware
4. `resources/js/pages/admin/settings/permissions/Index.vue` - Fixed permission grouping for new format

---

## Testing

### Manual Test Steps

1. **Edit Role Without Changing Name:**
   - Go to `/admin/settings/permissions`
   - Click "Edit" on any role
   - Select/deselect permissions
   - Click "Save" **without changing the role name**
   - ✅ Should save successfully

2. **Edit Role With New Name:**
   - Edit a role
   - Change the name to something unique
   - Select permissions
   - Click "Save"
   - ✅ Should save successfully

3. **Try Duplicate Name:**
   - Edit a role
   - Change name to match another existing role
   - Click "Save"
   - ✅ Should show error: "The name has already been taken"

4. **Verify Permission Grouping:**
   - Open create/edit role modal
   - ✅ Permissions should be grouped by domain (Posts, Pages, Calendar, etc.)
   - ✅ Both old (`posts.view`) and new (`view posts`) formats should group correctly

### Verification Commands

```bash
# Clear caches
./vendor/bin/sail artisan optimize:clear

# Verify permissions exist
./vendor/bin/sail artisan tinker --execute="echo Permission::count();"

# Test role update
./vendor/bin/sail artisan tinker
>>> $role = Role::find(2);
>>> $role->syncPermissions([1, 2, 3]); // Test with IDs
>>> $role->permissions; // Verify permissions attached
```

---

## Why These Fixes Work

### Permission IDs vs Names

**Laravel/Spatie Permission supports both:**
- `$role->syncPermissions([1, 2, 3])` ← **IDs** (what frontend sends)
- `$role->syncPermissions(['view posts', 'create posts'])` ← **Names**

The validation needed to match what the frontend sends (IDs).

### Route Model Binding

Laravel automatically resolves `{role}` in routes to a Role model. The validation rule now handles this correctly by checking if it's a model instance.

### Backward Compatibility

The permission grouping now handles **both** formats:
- Old: `posts.view`, `users.create`
- New: `view posts`, `create users`

This allows gradual migration from old to new permissions.

---

## Status

✅ **All Issues Resolved**

- Permission validation now accepts IDs (matches frontend behavior)
- Unique name validation properly excludes current role when editing
- Permission grouping works with both old and new formats
- Redundant controller middleware removed

The role editing feature should now work correctly for:
- Creating new roles
- Editing existing roles (with or without name change)
- Selecting/deselecting permissions
- Proper permission grouping in the UI
