# Route Restructure Summary
## Changes Made
### Backend Route Structure
All user-facing routes have been moved from the root `routes/` directory to nested structure under `routes/user/`:
#### Old Structure:
- `routes/profile.php` → `/profile`
- `routes/two-factor.php` → `/settings/two-factor`
#### New Structure:
- `routes/user/settings/profile.php` → `/user/settings/profile`
- `routes/user/settings/security/twofactor.php` → `/user/settings/security/two-factor`
- `routes/user/settings/security/webauthn.php` → `/user/settings/security/webauthn`
### Route Names Changed:
- `profile.edit` → `user.settings.profile.edit`
- `profile.update` → `user.settings.profile.update`
- `profile.destroy` → `user.settings.profile.destroy`
- `settings.two-factor.show` → `user.settings.security.two-factor.two-factor.show`
### Frontend Changes:
#### Updated Files:
1. **resources/js/routes/index.ts**
   - Added re-exports for `dashboard` and `settings`
   - Usage: `import { dashboard, settings } from '@/routes'`
2. **resources/js/components/UserMenuContent.vue**
   - Changed import from `'@/routes/settings'` to `'@/routes'`
   - Fixed typo: `settings.profile.ind` → `settings.profile.edit()`
3. **resources/js/layouts/settings/Layout.vue**
   - Updated import path
   - Changed route reference: `settings.security.twoFactor.twoFactor.show()`
   - Renamed sidebar item from "Profile" to "Settings"
4. **resources/js/pages/settings/Profile.vue**
   - Updated import path to use `'@/routes'`
5. **resources/js/pages/settings/Security.vue**
   - Removed unused imports and breadcrumbs
### Controller Updates:
- **ProfileController.php**: Updated redirect to use `user.settings.profile.edit`
### Test Updates:
- **ProfileUpdateTest.php**: Updated all route references to `user.settings.profile.*`
- **TwoFactorAuthenticationTest.php**: Updated to `user.settings.security.two-factor.show`
### Route Redirects Added:
- `/user/settings` → `/user/settings/profile` (authenticated users default to profile page)
## How to Use New Routes:
### Frontend (Vue/TypeScript):
```typescript
import { settings } from '@/routes'
// Profile routes
settings.profile.edit()      // GET /user/settings/profile
settings.profile.update()    // PATCH /user/settings/profile
settings.profile.destroy()   // DELETE /user/settings/profile
// Security/Two-Factor routes
settings.security.twoFactor.twoFactor.show()  // GET /user/settings/security/two-factor
settings.security.twoFactor.setup()            // GET /user/settings/security/two-factor/setup
settings.security.twoFactor.enable()           // POST /user/settings/security/two-factor/enable
settings.security.twoFactor.disable()          // DELETE /user/settings/security/two-factor/disable
// WebAuthn routes
settings.security.webauthn.register()             // POST /user/settings/security/webauthn/register
settings.security.webauthn.credentials.list()     // GET /user/settings/security/webauthn/credentials
```
### Backend (PHP):
```php
// Named routes
route('user.settings.profile.edit')
route('user.settings.profile.update')
route('user.settings.security.two-factor.two-factor.show')
// Redirects
to_route('user.settings.profile.edit')
```
## Notes:
- Wayfinder automatically generates route helpers from Laravel routes
- The `settings` export from `@/routes` provides access to all user settings routes
- All old route names have been updated throughout the codebase
