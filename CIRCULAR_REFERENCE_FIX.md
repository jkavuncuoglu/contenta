# Fixed: Circular Reference Error in Two-Factor Routes
## Problem
```
Uncaught ReferenceError: can't access lexical declaration 'twoFactor' before initialization
    at index.ts:161
```
## Root Cause
The route definition in `routes/user/settings/security/twofactor.php` had:
```php
Route::get('', [TwoFactorAuthenticationController::class, 'show'])
    ->name('two-factor.show');  // ❌ Creates duplicate "two-factor"
```
With the route group prefix and namespace, this created the route name:
`user.settings.security.two-factor.two-factor.show`
This caused Wayfinder to generate a circular reference:
```typescript
import twoFactor from './two-factor'  // Imports from subdirectory
// ...
const twoFactor = {  // Creates new const with same name
    twoFactor: Object.assign(twoFactor, twoFactor),  // 💥 Circular!
}
```
## Solution
Changed the route name from `'two-factor.show'` to `'show'`:
```php
Route::get('', [TwoFactorAuthenticationController::class, 'show'])
    ->name('show');  // ✅ No duplication
```
Now the route name is: `user.settings.security.two-factor.show`
## Files Changed
### Backend
1. **routes/user/settings/security/twofactor.php**
   - Changed `->name('two-factor.show')` to `->name('show')`
### Tests
2. **tests/Feature/Settings/TwoFactorAuthenticationTest.php**
   - Updated all route references from `'settings.security.two-factor.show'`
   - To: `'user.settings.security.two-factor.show'`
### Frontend
3. **resources/js/layouts/settings/Layout.vue**
   - Can now use: `settings.security.twoFactor.show()`
   - Instead of hardcoded URL
## How to Use
### Frontend (Vue/TypeScript)
```typescript
import settings from '@/routes/user/settings'
// Security/Two-Factor routes
settings.security.twoFactor.show()    // GET /user/settings/security/two-factor
settings.security.twoFactor.setup()   // GET /user/settings/security/two-factor/setup
settings.security.twoFactor.enable()  // POST /user/settings/security/two-factor/enable
```
### Backend (PHP)
```php
route('user.settings.security.two-factor.show')
to_route('user.settings.security.two-factor.show')
```
## Verification
Run: `php artisan route:list --name=two-factor`
Should show:
- ✅ `user.settings.security.two-factor.show`
- ❌ NOT `user.settings.security.two-factor.two-factor.show`
## Next Steps
1. Restart Vite dev server to regenerate route helpers
2. Clear browser cache to ensure fresh JS modules load
3. Verify no more "can't access lexical declaration" errors
