# Contenta: Full Documentation & Architecture

A modular Laravel 12 + Vue 3 (Inertia.js) application scaffold for content‑centric platforms (CMS / knowledge base / publishing) with opinionated domain separation, rich publishing models, and advanced security (WebAuthn security keys + TOTP two‑factor, recovery codes, roles & permissions).

## Quick Documentation Links
- Setup & Local Development: `contenta/documentation/SETUP.md`
- Features Overview: `contenta/documentation/FEATURES.md`
- Backend Guide (Laravel): `contenta/documentation/BACKEND.md`
- Frontend Guide (Vue + Inertia): `contenta/documentation/FRONTEND.md`
- Security Overview: `contenta/documentation/SECURITY.md`
- WebAuthn Implementation: `contenta/documentation/WEBAUTHN_IMPLEMENTATION.md`
- Releases & Forking: `contenta/documentation/RELEASES.md`
- Contributing Guide: `contenta/documentation/CONTRIBUTING.md`
- Legal & Risk Notice: `contenta/documentation/LEGAL.md`

## 1. Key Features
- Laravel 12 (PHP 8.4) with Inertia.js (Vue 3 + TypeScript)
- WebAuthn (security keys) and TOTP + recovery codes for multi-factor auth
- Domain oriented organization under `app/Domains/*`
- Auth/session management via Laravel Fortify
- Roles & permissions (spatie/laravel-permission)
- Media handling (spatie/laravel-medialibrary)
- Activity logging (spatie/laravel-activitylog)
- Rich Post & Page models (taxonomy: Categories, Tags)
- Horizon-ready for queued jobs
- Tailwind CSS v4 + Vite + TypeScript + ESLint + Prettier
- Pest (unit/feature) + Playwright (E2E)

## 2. Security Highlights (Summary)
- WebAuthn security keys (platform & roaming authenticators)
- TOTP 2FA with recovery codes lifecycle
- CSRF protection on all state-changing requests
- Server-side validation for authentication factors & credential attestation
- Role & permission enforcement (foundation for granular policy layer)
- Unique per-credential identification and last-used tracking
  - Details: `contenta/documentation/SECURITY.md` and `contenta/documentation/WEBAUTHN_IMPLEMENTATION.md`

## 3. Technology Stack
| Layer | Tools / Packages |
|-------|------------------|
| Backend | Laravel Framework 12, Fortify, Horizon |
| Security | WebAuthn, Google2FA (pragmarx/google2fa-laravel + spomky-labs/otphp) |
| Auth UX | Inertia + Vue forms (Fortify endpoints) |
| Permissions | spatie/laravel-permission |
| Media | spatie/laravel-medialibrary |
| Logging & Auditing | spatie/laravel-activitylog |
| Frontend | Vue 3, Inertia, Tailwind 4, Reka UI, lucide (→ Iconify planned) |
| Tooling | Vite 7, TypeScript, ESLint, Prettier, Pint |
| Tests | Pest (backend) + Playwright (E2E) |
| Queues / Jobs | Database queue (default), Horizon available |

## 4. Quick Start (Abbreviated)
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
composer dev   # launches PHP server + queue + logs + Vite
```
Visit: http://localhost:8000 (or configured host). Full setup: `contenta/documentation/SETUP.md`.

## 5. Setup & Installation (Expanded)
Prerequisites: PHP 8.4+, Composer, Node 20+, SQLite/MySQL, Redis (optional), npm/pnpm.
```bash
# 1. Backend deps
composer install
# 2. Environment
cp .env.example .env
php artisan key:generate
# 3. Database (adjust .env for MySQL/Postgres if needed)
php artisan migrate
# 4. Frontend deps
npm install
# 5. Run dev stack
composer dev
```
Optional: `php artisan db:seed` for sample data (when seeders available). See `contenta/documentation/SETUP.md`.

## 6. Project Structure (High Level)
```
app/
  Domains/
    ContentManagement/
      Posts/
      Pages/
      Categories/
      Tags/
    Security/
      ... (TwoFactor, Roles, etc.)
    Settings/
      Http/Controllers/Settings
  Http/
    Controllers/
    Middleware/
    Requests/
  Models/
  Providers/
config/
routes/
resources/
public/
tests/
```

### Domain Organization Rationale
Each bounded context encapsulates controllers, services, models, (future: policies, jobs, events, value objects) reducing coupling and easing growth.

## 7. Service Container Bindings
`App\Providers\AppServiceProvider` binds:
- `TwoFactorAuthenticationServiceInterface` → `TwoFactorAuthenticationService`
- `PagesServiceContract` → `PagesService`
(2FA service implementation currently a placeholder.)

## 8. Backend Domains Overview
| Domain | Highlights |
|--------|-----------|
| Content Management | Posts (media, taxonomy), Pages (CRUD via service), Categories (hierarchy), Tags (labeling) |
| Security | Two-factor flows, roles & permissions endpoints |
| Settings | User profile/password/2FA screens, site settings placeholder |

## 9. Routes Summary (Abridged)
Public/Auth:
- `GET /` → Welcome (Inertia)
- `GET /dashboard` (auth+verified) → Dashboard
- Registration, login, password reset, verification via Fortify
- AJAX: `GET /check-email`, `GET /check-username`
Two-Factor (auth): status/setup/enable/disable, recovery codes lifecycle.
Admin (`/admin`): dashboard + Posts/Categories/Tags/Pages CRUD, settings include.
Settings (`/settings`): site, user management (profile/password/2FA), permissions (roles).

## 10. Models (Selected Details)
### Post
- Metadata (SEO/OpenGraph/Twitter), versioning fields, taxonomy relations, media collections: `featured_images`, `gallery`, `attachments`.
- Activity logging (dirty-only) and domain methods bridging to `PostAggregate`.
### Category
- Hierarchical parent/children, full path & depth accessors, featured ordering scopes.
### Page
- Minimal static content: `title`, `slug`, `content`, `published`.
### Tag
- Lightweight labeling (posts relation).

## 11. Services
### PagesService
Singleton implementing `PagesServiceContract` for CRUD, enabling future caching/events.
### TwoFactorAuthenticationService
Needs implementation: secret generation, provisioning URI/QR, enable/disable, recovery codes lifecycle, token validation.

## 12. Frontend Stack
- Inertia pages in `resources/js/Pages/*`
- Tailwind 4 + PostCSS; utility composition via class variance libraries.
- Icon migration planned (lucide → Iconify + Material Light set).
- Vite handles dev & SSR builds (`npm run build:ssr`).

## 13. Tooling & Scripts
Composer:
- `composer dev` (PHP server + queue listener + pail log + Vite)
- `composer dev:ssr`
- `composer test`
NPM:
- `npm run dev` / `build` / `build:ssr`
- `npm run lint` / `format` / `format:check`
- `npm run test:e2e`

## 14. Current Gaps / TODO
Original list:
1. Replace residual API calls with pure Inertia flows.
2. Update state stores to use Inertia endpoints.
3. Replace lucide icons with Iconify.
4. Sidebar expansion logic.
Additional suggestions:
- Implement full TwoFactorAuthenticationService + tests.
- Add Tags/Categories persistence endpoints if missing.
- Introduce Policies/Gates for admin & content.
- DTO/Resource transformers for Post/Category.
- Missing migrations for pivot tables (taxonomy) if absent.
- Seeders for sample content & roles.
- Caching (tagged caches for category tree/navigation).
- Refine activity log scope & redact sensitive fields.

## 15. Architectural Principles
- Bounded contexts reduce coupling.
- Services introduced only where abstraction adds near-term value.
- Interfaces allow future enhancements (caching, event sourcing) without controller rewrites.

## 16. Adding a New Domain (Example)
1. `app/Domains/YourDomain` directory.
2. Subfolders: `Models`, `Http/Controllers`, `Services`, `Policies`.
3. Service contracts + provider binding.
4. Migrations & factories.
5. Route file included from `web.php`/`admin.php`.
6. Inertia pages under `resources/js/Pages/YourDomain`.

## 17. Security Considerations (Extended)
- Password confirmation recommended for 2FA state changes.
- Rate-limit sensitive endpoints (2FA, password, recovery codes).
- Add Policies for Posts/Pages/Taxonomy prior to multi-role rollout.
- Strengthen slug uniqueness (consider DB constraints + validation rules).
- Enforce HTTPS & secure cookies; rotate secrets.

## 18. Testing
Backend (Pest):
```bash
php artisan test
# or
./vendor/bin/pest
```
E2E (Playwright):
```bash
npx playwright install --with-deps
npm run test:e2e
```
Seed test user & storage state for auth flows.

## 19. Code Quality
- PHP: Pint (`./vendor/bin/pint`)
- JS/TS: ESLint + Prettier (`npm run lint`, `npm run format`)
- Type checking: consider adding `vue-tsc --noEmit` in CI.

## 20. Environment / Configuration Notes
Key `.env`:
- `SESSION_DRIVER=database`
- `QUEUE_CONNECTION=database` (Redis + Horizon for prod recommended)
- `LOG_CHANNEL=stack`
- `BCRYPT_ROUNDS` (lower locally for speed)

## 21. Deployment Notes
```bash
npm run build        # Frontend assets
npm run build:ssr    # (Optional) SSR build
php artisan config:cache route:cache view:cache
php artisan queue:work  # or Horizon
php artisan storage:link
```
Configure `FILESYSTEM_DISK` (e.g., s3) and queue backend; use supervisors for workers.

## 22. Glossary
| Term | Description |
|------|-------------|
| Inertia Response | Controller returns Vue component name + props, bypassing separate JSON API layer. |
| Aggregate | Cohesive domain state snapshot (e.g., `PostAggregate`). |
| Recovery Codes | One-time backup codes for 2FA if primary factor unavailable. |

## 23. Status Summary
| Area | Status |
|------|--------|
| Pages CRUD | Implemented (service-backed) |
| Posts Admin | List/create/edit views; persistence logic partially shown |
| Taxonomy | Skeleton CRUD; verify store/update/delete completeness |
| Two-Factor | Routes + controller; service logic pending |
| Roles/Permissions | Basic CRUD present |
| Tests | Framework present; coverage TBD |
| E2E | Playwright configured; tests not enumerated |

## 24. License & Disclaimer
- License: `MIT`
- Disclaimer (with contribution expectation): `DISCLAIMER.md`
- Extended legal summary: `contenta/documentation/LEGAL.md`
- Sponsor the project: https://buymeacoffee.com/jkavuncuoglu

## 25. Contribution Requirement
See disclaimer for community expectation of reciprocal contribution (issue, docs, code, security report, sponsorship). Formal guidelines: `contenta/documentation/CONTRIBUTING.md`.

## 26. Releases & Forking
Instructions & placeholder release link: `contenta/documentation/RELEASES.md`.

## 27. Further Documentation
For deeper dives (backend internals, WebAuthn flows, security posture, setup), consult the documentation folder referenced in the Quick Links above.

---
This README merges original high-level architecture and separate documentation index/security highlights into a single comprehensive entry point.
