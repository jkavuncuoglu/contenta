# Guard Name Validation Fix

**Date:** 2025-12-04
**Issue:** Role uniqueness validation needs to be scoped by guard_name, allowing "admin" for both web and api guards

---

## Problem

The unique validation for role names was checking uniqueness across ALL guards, preventing creation of roles with the same name but different guards. This is incorrect because Spatie Permission allows the same role name for different guards (web vs api).

Additionally, permissions were only being created for the "web" guard, preventing API roles from having proper permissions.

**Example:**
- Role "admin" with guard "web" should be unique only within guard "web"
- Role "admin" with guard "api" should be allowed (different guard)
- Previously: Creating "admin" for "api" would fail if "admin" for "web" existed
- Previously: API roles couldn't be assigned permissions because no API guard permissions existed

---

## Solution

### Backend Changes

**1. Updated Validation Rules**

Modified both `UpdateRoleRequest` and `StoreRoleRequest` to:
- Accept `guard_name` as an optional field (defaults to 'web')
- Scope uniqueness validation by the provided guard_name
- Scope permission existence validation by the provided guard_name
- Use Laravel's `Rule::unique()` and `Rule::exists()` facades for proper scoping

**File:** `app/Domains/Security/Http/Requests/Admin/UpdateRoleRequest.php`

```php
<?php

namespace App\Domains\Security\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update roles');
    }

    public function rules(): array
    {
        $role = $this->route('role');
        $roleId = $role instanceof \Spatie\Permission\Models\Role ? $role->id : $role;
        $guardName = $this->input('guard_name', 'web');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->ignore($roleId)
                    ->where('guard_name', $guardName),
            ],
            'guard_name' => ['sometimes', 'string', 'in:web,api'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')->where('guard_name', $guardName),
            ],
        ];
    }
}
```

**File:** `app/Domains/Security/Http/Requests/Admin/StoreRoleRequest.php`

```php
<?php

namespace App\Domains\Security\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create roles');
    }

    public function rules(): array
    {
        $guardName = $this->input('guard_name', 'web');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', $guardName),
            ],
            'guard_name' => ['sometimes', 'string', 'in:web,api'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')->where('guard_name', $guardName),
            ],
        ];
    }
}
```

**2. Updated Controller**

Modified `RolesController` to handle `guard_name` from request:

**File:** `app/Domains/Security/Http/Controllers/Admin/RolesController.php`

```php
public function index(): Response
{
    $roles = Role::with(['permissions:id,name,guard_name'])->withCount('users')->orderBy('name')->get();

    // Get all permissions grouped by guard for frontend filtering
    $permissions = Permission::orderBy('name')->get(['id', 'name', 'guard_name']);

    return Inertia::render('admin/settings/permissions/Index', [
        'roles' => $roles,
        'permissions' => $permissions,
    ]);
}

public function store(StoreRoleRequest $request): RedirectResponse
{
    $data = $request->validated();
    $role = Role::create([
        'name' => $data['name'],
        'guard_name' => $data['guard_name'] ?? 'web',
    ]);

    if (! empty($data['permissions'])) {
        $role->syncPermissions($data['permissions']);
    }

    return redirect()->route('settings.permissions.index')
        ->with('success', 'Role created successfully.');
}

public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
{
    if ($role->name === 'super-admin' && $request->input('name') !== $role->name) {
        return back()->with('error', 'The super-admin role name cannot be changed.');
    }

    $data = $request->validated();
    $role->name = $data['name'];

    // Update guard_name if provided (and role is not super-admin)
    if (isset($data['guard_name']) && $role->name !== 'super-admin') {
        $role->guard_name = $data['guard_name'];
    }

    $role->save();

    $role->syncPermissions($data['permissions'] ?? []);

    return redirect()->route('settings.permissions.index')
        ->with('success', 'Role updated successfully.');
}
```

**3. Updated Seeder**

Modified `RolesAndPermissionsSeeder` to create permissions for BOTH guards:

**File:** `database/seeders/RolesAndPermissionsSeeder.php`

Key changes:
- Create all permissions for both 'web' and 'api' guards
- Create all roles for both guards
- Assign permissions separately for web and api roles

```php
// Create NEW space-separated permissions for BOTH guards
foreach (['web', 'api'] as $guard) {
    foreach ($permissions as $permission) {
        Permission::firstOrCreate([
            'name' => $permission,
            'guard_name' => $guard,
        ]);
    }
}

// Create roles idempotently for BOTH guards
$superAdminWeb = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
$adminWeb = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
// ... other web roles

$superAdminApi = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'api']);
$adminApi = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
// ... other api roles

// Assign permissions separately for each guard
$superAdminWeb->syncPermissions(Permission::where('guard_name', 'web')->get());
$superAdminApi->syncPermissions(Permission::where('guard_name', 'api')->get());
```

### Frontend Changes

**4. Updated Vue Component**

Added `guard_name` field to the role form and filtered permissions by guard:

**File:** `resources/js/pages/admin/settings/permissions/Index.vue`

**TypeScript Interface:**
```typescript
interface RoleFormState {
    id: number | null;
    name: string;
    guard_name: string; // Added
    permissions: number[];
}

const roleForm = ref<RoleFormState>({
    id: null,
    name: '',
    guard_name: 'web', // Default to web
    permissions: []
});
```

**Permission Filtering:**
```typescript
// Filter permissions by selected guard
const filteredPermissions = computed(() => {
    return allPermissions.value.filter(
        (p) => p.guard_name === roleForm.value.guard_name
    );
});

// Group permissions (using filtered list)
const permissionGroups = computed(() => {
    const groups: Record<string, Permission[]> = {};
    filteredPermissions.value.forEach((p) => {
        // Support both old dot-notation and new space-separated format
        let seg: string;
        if (p.name.includes('.')) {
            seg = p.name.split('.')[0] || 'general';
        } else {
            const parts = p.name.split(' ');
            seg = parts.length > 1 ? parts[parts.length - 1] : 'general';
        }
        const key = seg.toLowerCase();
        (groups[key] ||= []).push(p);
    });
    return groups;
});
```

**Form State Management:**
```typescript
const resetForm = () => {
    roleForm.value = { id: null, name: '', guard_name: 'web', permissions: [] };
};

const openEdit = (role: Role) => {
    roleForm.value = {
        id: role.id,
        name: role.name,
        guard_name: role.guard_name,
        permissions: (role.permissions || []).map((p) => p.id),
    };
};

const submit = () => {
    const payload = {
        name: roleForm.value.name,
        guard_name: roleForm.value.guard_name,
        permissions: roleForm.value.permissions,
    };
    // ... submit logic
};
```

**UI Addition:**
```vue
<div>
    <label for="guard-name" class="mb-1 block text-sm font-medium">
        Guard
    </label>
    <select
        id="guard-name"
        v-model="roleForm.guard_name"
        :disabled="isEditing"
        class="w-full rounded-md px-3 py-2 border-neutral-300"
    >
        <option value="web">Web</option>
        <option value="api">API</option>
    </select>
    <p class="mt-1 text-xs text-neutral-500">
        Guard type for this role. Cannot be changed after creation.
    </p>
</div>
```

**Note:** Guard selection is disabled when editing to prevent accidental guard changes.

---

## How It Works Now

### Creating Roles

1. User selects guard (web or api) from dropdown (defaults to 'web')
2. Permission list is filtered to show ONLY permissions for the selected guard
3. Frontend sends `{ name: "admin", guard_name: "web", permissions: [...] }`
4. Backend validates uniqueness only within that guard
5. Backend validates permissions exist for that guard
6. Can create "admin" for both web and api guards

**Example:**
- Create role "admin" with guard "web" ✅
- Create role "admin" with guard "api" ✅ (allowed - different guard)
- Create another "admin" with guard "web" ❌ (rejected - duplicate in same guard)
- Assign permission ID 98 (web guard) to web role ✅
- Assign permission ID 98 (web guard) to api role ❌ (rejected - wrong guard)

### Updating Roles

1. Guard field is pre-filled with existing role's guard
2. Guard dropdown is disabled to prevent changes
3. Permissions are filtered to match the role's guard
4. Backend checks role name and prevents super-admin guard changes
5. Name uniqueness validated within the role's current guard
6. Permission assignment validated within the role's guard

**Example:**
- Edit "admin" (web) and keep name ✅
- Edit "admin" (web) and rename to "editor" (if unique in web) ✅
- Edit "admin" (api) and keep name ✅ (validated within api guard only)

### Permission Filtering

When creating or editing a role:
- **Web guard selected:** Only shows web guard permissions (83 permissions)
- **API guard selected:** Only shows api guard permissions (83 permissions)
- Backend validation ensures no cross-guard permission assignment
- Spatie Permission enforces guard matching at database level

---

## Validation Logic

### Uniqueness Scope

```php
Rule::unique('roles', 'name')
    ->where('guard_name', $guardName)
    ->ignore($roleId) // When updating
```

**Translation:**
"Role name must be unique WHERE guard_name equals the provided guard, ignoring the current role ID (when editing)"

### Permission Existence Scope

```php
Rule::exists('permissions', 'id')
    ->where('guard_name', $guardName)
```

**Translation:**
"Permission ID must exist WHERE guard_name equals the role's guard"

### Database State Example

```
Roles:
ID  | Name          | Guard |
----|---------------|-------|
1   | super-admin   | web   |
7   | super-admin   | api   |
2   | admin         | web   |
8   | admin         | api   |

Permissions:
ID  | Name          | Guard |
----|---------------|-------|
98  | access admin  | web   |
182 | access admin  | api   |
```

**Validation Results:**
- Create "admin" with guard "web" → ❌ Fails (ID 2 exists)
- Create "admin" with guard "api" → ❌ Fails (ID 8 exists)
- Create "moderator" with guard "web" → ✅ Passes (unique in web)
- Create "moderator" with guard "api" → ✅ Passes (unique in api)
- Assign permission 98 to web role → ✅ Passes (same guard)
- Assign permission 98 to api role → ❌ Fails (wrong guard)
- Assign permission 182 to api role → ✅ Passes (same guard)

---

## Benefits

1. **Proper Multi-Guard Support**
   - Can have identical role names for different guards
   - Each guard has its own complete set of permissions
   - Follows Spatie Permission's design pattern

2. **Prevents Cross-Guard Errors**
   - Frontend filters permissions by guard automatically
   - Backend validates permissions match role's guard
   - Database enforces guard consistency

3. **Flexible Architecture**
   - Web-facing roles separate from API-facing roles
   - Same permissions structure for both guards
   - Independent permission management per guard

4. **User-Friendly**
   - Guard selector in UI makes it clear which guard is being used
   - Filtered permission list prevents confusion
   - Prevents accidental guard changes on existing roles

5. **Backward Compatible**
   - Defaults to 'web' guard if not specified
   - Existing web roles unaffected
   - Old dot-notation permissions maintained temporarily

---

## Files Modified

1. `app/Domains/Security/Http/Requests/Admin/UpdateRoleRequest.php`
2. `app/Domains/Security/Http/Requests/Admin/StoreRoleRequest.php`
3. `app/Domains/Security/Http/Controllers/Admin/RolesController.php`
4. `database/seeders/RolesAndPermissionsSeeder.php`
5. `resources/js/pages/admin/settings/permissions/Index.vue`

---

## Testing

### Database Verification

```bash
./vendor/bin/sail artisan tinker

# Check permissions by guard
Permission::where('guard_name', 'web')->count();  // Should show 212
Permission::where('guard_name', 'api')->count();  // Should show 83

# Check roles by guard
Role::where('guard_name', 'web')->pluck('name');
Role::where('guard_name', 'api')->pluck('name');

# Verify a specific permission exists in both guards
Permission::where('name', 'access admin')->get(['id', 'name', 'guard_name']);
```

### Manual Tests

**Test 1: Create role with web guard**
- Open /admin/settings/permissions
- Click "Create Role"
- Enter name "moderator", select guard "Web"
- Select permissions (should show only web permissions)
- Save
- ✅ Should create successfully

**Test 2: Create role with same name, different guard**
- Create role "moderator" with guard "API"
- Select permissions (should show only api permissions)
- ✅ Should create successfully (different guard)

**Test 3: Try duplicate in same guard**
- Try creating another "moderator" with guard "Web"
- ❌ Should fail with "The name has already been taken"

**Test 4: Edit role keeping name**
- Edit "admin" (web) role
- Change permissions but keep name
- ✅ Should save successfully

**Test 5: Guard field disabled on edit**
- Edit any role
- Guard dropdown should be disabled
- ✅ Cannot change guard after creation

**Test 6: Permission filtering**
- Create new role, select "Web" guard
- Count visible permissions
- Change to "API" guard
- Permission list should update to show only API permissions
- ✅ Permission list filters correctly

---

## Status

✅ **Complete**

- Guard-scoped uniqueness validation implemented
- Guard-scoped permission existence validation implemented
- Frontend supports guard selection
- Frontend filters permissions by selected guard
- Controller handles guard_name parameter
- Seeder creates permissions for both web and api guards
- Seeder creates roles for both guards
- Existing roles unaffected
- Defaults to 'web' for backward compatibility
- Spatie Permission guard consistency enforced

## Running the Seeder

To populate the database with all permissions and roles:

```bash
./vendor/bin/sail artisan db:seed --class=RolesAndPermissionsSeeder
./vendor/bin/sail artisan permission:cache-reset
```

This will:
- Create 83 permissions for web guard
- Create 83 permissions for api guard
- Create 6 standard roles for each guard (super-admin, admin, editor, author, contributor, subscriber)
- Assign appropriate permissions to each role
- Maintain backward compatibility with old dot-notation permissions (web guard only)
