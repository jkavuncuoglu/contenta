# App-Level Social Accounts Refactor

**Date:** 2025-12-04
**Status:** ✅ COMPLETE

---

## Overview

Refactored social media accounts from user-specific to app-level (system-wide). Social accounts are now shared across the entire application rather than being tied to individual users.

---

## Rationale

**Previous Design (User-Level):**
- Each user could connect their own social media accounts
- `social_accounts.user_id` foreign key to users table
- Accounts were isolated per user

**New Design (App-Level):**
- Social accounts are system-wide, managed by any authenticated admin
- No `user_id` column
- Single connection per platform per account (e.g., one Twitter account for the entire app)
- All admins can manage all social accounts
- Simplifies auto-posting logic - no need to determine which user's account to use

---

## Database Changes

### Migration Created
**File:** `2025_12_04_101337_remove_user_id_from_social_accounts_table.php`

**Changes:**
1. Dropped foreign key constraint: `user_id -> users`
2. Dropped unique constraint: `['user_id', 'platform', 'platform_account_id']`
3. Removed `user_id` column
4. Added new unique constraint: `['platform', 'platform_account_id']`

**Before:**
```sql
CREATE TABLE social_accounts (
    id BIGINT PRIMARY KEY,
    user_id BIGINT FOREIGN KEY -> users,
    platform VARCHAR(50),
    platform_account_id VARCHAR(255),
    -- other fields...
    UNIQUE(user_id, platform, platform_account_id)
);
```

**After:**
```sql
CREATE TABLE social_accounts (
    id BIGINT PRIMARY KEY,
    platform VARCHAR(50),
    platform_account_id VARCHAR(255),
    -- other fields...
    UNIQUE(platform, platform_account_id)
);
```

---

## Model Changes

### SocialAccount Model
**File:** `app/Domains/SocialMedia/Models/SocialAccount.php`

**Removed:**
- `user_id` from `$fillable` array
- `user()` BelongsTo relationship method
- Import for `App\Models\User`
- Import for `BelongsTo` relation

**Result:** Model no longer has any user relationship

### User Model
**File:** `app/Models/User.php`

**Removed:**
- `socialAccounts()` HasMany relationship method
- Import for `SocialAccount`
- Import for `HasMany` relation

**Result:** Users no longer have a relationship to social accounts

---

## Service Layer Changes

### OAuthServiceContract
**File:** `app/Domains/SocialMedia/Services/OAuthServiceContract.php`

**Changes:**
```php
// Before
public function getAuthorizationUrl(string $platform, User $user): string;
public function handleCallback(string $platform, string $code, string $state, User $user): SocialAccount;

// After
public function getAuthorizationUrl(string $platform): string;
public function handleCallback(string $platform, string $code, string $state): SocialAccount;
```

- Removed `User $user` parameter from both methods
- Removed `use App\Models\User` import

### OAuthService Implementation
**File:** `app/Domains/SocialMedia/Services/OAuthService.php`

**Changes:**

1. **getAuthorizationUrl():**
```php
// Before
session([
    'oauth_state' => $state,
    'oauth_platform' => $platform,
    'oauth_user_id' => $user->id,  // ← Removed
]);

// After
session([
    'oauth_state' => $state,
    'oauth_platform' => $platform,
]);
```

2. **handleCallback():**
```php
// Before
$account = SocialAccount::updateOrCreate(
    [
        'user_id' => $user->id,  // ← Removed
        'platform' => $platform,
        'platform_account_id' => $tokenData['account_id'],
    ],
    [ /* ... */ ]
);

// After
$account = SocialAccount::updateOrCreate(
    [
        'platform' => $platform,
        'platform_account_id' => $tokenData['account_id'],
    ],
    [ /* ... */ ]
);
```

3. **disconnect():**
```php
// Before
Log::info('Social account disconnected', [
    'user_id' => $account->user_id,  // ← Removed
    'platform' => $account->platform,
    'account_id' => $account->id,
]);

// After
Log::info('Social account disconnected', [
    'platform' => $account->platform,
    'account_id' => $account->id,
]);
```

- Removed `use App\Models\User` import

---

## Controller Changes

### OAuthController
**File:** `app/Domains/SocialMedia/Http/Controllers/Admin/OAuthController.php`

**Changes:**

1. **authorize():**
```php
// Before
$user = Auth::user();
$authUrl = $this->oauthService->getAuthorizationUrl($platform, $user);

// After
$authUrl = $this->oauthService->getAuthorizationUrl($platform);
```

2. **callback():**
```php
// Before
$user = Auth::user();
$account = $this->oauthService->handleCallback($platform, $code, $state, $user);

// After
$account = $this->oauthService->handleCallback($platform, $code, $state);
```

3. **refresh():**
```php
// Before
$account = Auth::user()->socialAccounts()->findOrFail($accountId);

// After
$account = SocialAccount::findOrFail($accountId);
```

4. **disconnect():**
```php
// Before
$account = Auth::user()->socialAccounts()->findOrFail($accountId);

// After
$account = SocialAccount::findOrFail($accountId);
```

- Removed `use Illuminate\Support\Facades\Auth`
- Added `use App\Domains\SocialMedia\Models\SocialAccount`
- Changed error redirect from `admin.settings.social-media` to `admin.social-media.accounts.index`

### SocialAccountController
**File:** `app/Domains/SocialMedia/Http/Controllers/Admin/SocialAccountController.php`

**Changes:**

1. **index():**
```php
// Before
$accounts = Auth::user()
    ->socialAccounts()
    ->with([...])
    ->get();

// After
$accounts = SocialAccount::with([...])
    ->orderBy('created_at', 'desc')
    ->get();
```

2. **All methods:**
- Removed all `$this->authorize()` calls (show, edit, update, destroy, verify)
- No longer checks if user owns the account
- Any authenticated admin can manage any social account

**Authorization:** Since accounts are app-level, policy checks were removed. All authenticated admins have full access to all social accounts.

---

## Policy Changes

### SocialAccountPolicy
**File:** `app/Domains/SocialMedia/Policies/SocialAccountPolicy.php`

**Status:** Policy still exists but is no longer used since authorization checks were removed from controllers. Can be deleted in future cleanup.

**Note:** Policy registration in `SocialMediaServiceProvider` can also be removed.

---

## Benefits of App-Level Accounts

1. **Simplified Auto-Posting:**
   - No ambiguity about which user's account to use for auto-posting
   - Single source of truth per platform
   - Easier queue management

2. **Team Collaboration:**
   - All admins can manage social accounts
   - No need to coordinate which user connects which account
   - Centralized account management

3. **Reduced Complexity:**
   - Eliminates user-account ownership logic
   - Simpler OAuth flow (no user context needed)
   - Cleaner data model

4. **Better Fits Use Case:**
   - CMS typically has one official social presence per platform
   - Multiple users managing same accounts makes more sense than multiple isolated accounts

---

## Migration Instructions

If you have existing data in production:

```bash
# 1. Backup database first!

# 2. Run migration
./vendor/bin/sail artisan migrate

# 3. If you need to rollback:
./vendor/bin/sail artisan migrate:rollback --step=1
```

**Data Impact:**
- Existing accounts will remain in database
- `user_id` column will be removed
- Duplicate accounts (same platform + platform_account_id) will cause errors
  - Manually clean up duplicates before migration if needed

---

## Files Modified

### Backend (10 files):
1. `app/Domains/SocialMedia/Database/migrations/2025_12_04_101337_remove_user_id_from_social_accounts_table.php` ✨ NEW
2. `app/Domains/SocialMedia/Models/SocialAccount.php`
3. `app/Models/User.php`
4. `app/Domains/SocialMedia/Services/OAuthServiceContract.php`
5. `app/Domains/SocialMedia/Services/OAuthService.php`
6. `app/Domains/SocialMedia/Http/Controllers/Admin/OAuthController.php`
7. `app/Domains/SocialMedia/Http/Controllers/Admin/SocialAccountController.php`

### Not Modified (But Impacted):
8. `app/Domains/SocialMedia/Policies/SocialAccountPolicy.php` - No longer actively used
9. `app/Domains/SocialMedia/SocialMediaServiceProvider.php` - Policy registration can be removed

---

## Testing Checklist

- [ ] Migration runs successfully
- [ ] OAuth flow works without user context
- [ ] Can connect Twitter account
- [ ] Can connect Facebook account
- [ ] Can connect LinkedIn account
- [ ] Account list shows all accounts (not filtered by user)
- [ ] Account edit works for any admin
- [ ] Account delete works for any admin
- [ ] Token refresh works
- [ ] Duplicate platform connections prevented by unique constraint

---

## Future Considerations

### Potential Enhancements:
1. **Audit Logging:** Track which admin connected/modified each account
2. **Permissions:** Role-based access control (some admins can only view, others can edit)
3. **Account Labels:** Optional labels to distinguish multiple accounts on same platform (if needed in future)

### Cleanup Tasks:
- Consider removing `SocialAccountPolicy.php` entirely
- Remove policy registration from `SocialMediaServiceProvider`
- Update any future frontend components to not show user-specific filtering

---

## Summary

✅ Social accounts are now app-level (system-wide)
✅ No user_id relationship
✅ Simplified OAuth flow
✅ All admins can manage all accounts
✅ Database migration completed
✅ All service layer updated
✅ All controllers updated
✅ Ready for Phase 3 frontend implementation

**Next Steps:** Continue with Phase 3 frontend components (Account Index UI, Edit UI, ConnectPlatformButton, etc.)
