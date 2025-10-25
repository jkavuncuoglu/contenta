# Contenta

A modular Laravel 12 + Vue 3 (Inertia.js) application scaffold for content‑centric platforms (CMS / knowledge base / publishing) with opinionated domain separation and modern development tooling.

## 1. Key Features
- Laravel 12 (PHP 8.4) with Inertia.js (Vue 3 + TypeScript)
- Domain oriented organization under `app/Domains/*`
- Auth & session management via Laravel Fortify
- Role & permission system (spatie/laravel-permission)
- Media handling (spatie/laravel-medialibrary)
- Activity logging (spatie/laravel-activitylog)
- Two‑factor authentication (TOTP + recovery codes)
- Rich Post & Page models with taxonomy (Categories, Tags) foundations
- Horizon-ready for queued jobs (package present)
- Tailwind CSS v4 + Vite + TypeScript + ESLint + Prettier
- Pest for unit/feature tests + Playwright for E2E

## 2. Technology Stack
| Layer | Tools / Packages |
|-------|------------------|
| Backend | Laravel Framework 12, Fortify, Horizon |
| Security | Google2FA (pragmarx/google2fa-laravel + spomky-labs/otphp) |
| Auth UX | Inertia + Vue forms (Fortify endpoints) |
| Permissions | spatie/laravel-permission |
| Media | spatie/laravel-medialibrary |
| Logging & Auditing | spatie/laravel-activitylog |
| Frontend | Vue 3, Inertia, Tailwind 4, Reka UI, lucide (pending migration to Iconify per TODO) |
| Tooling | Vite 7, TypeScript, ESLint, Prettier, Pint |
| Tests | Pest (backend) + Playwright (E2E) |
| Queues / Jobs | Database queue (default), Horizon available |

## 3. Project Structure (High Level)
```
app/
  Domains/
    ContentManagement/
      Posts/ (Models, Http/Controllers/Admin, Aggregates, etc.)
      Pages/ (Controllers, Models, Services)
      Categories/
      Tags/
    Security/
      ... (TwoFactor, Roles, etc.)
    Settings/
      Http/Controllers/Settings
  Http/
    Controllers/ (auth & shared controllers)
    Middleware/
    Requests/
  Models/ (User, shared models)
  Providers/ (App / Fortify / etc.)
config/
routes/ (web, auth, admin, two-factor, settings, console)
resources/ (views via Inertia + Vue components, CSS)
public/
tests/ (Feature, Unit, e2e/ for Playwright)
```

### Domain Organization Rationale
Each bounded context (ContentManagement, Security, Settings) encapsulates:
- Http Controllers (UI / Admin endpoints)
- Services (application layer abstractions)
- Models / Aggregates
- Future: Policies, Jobs, Events, Value Objects

This reduces coupling and keeps growth manageable as features expand.

## 4. Service Container Bindings
Defined in `App\Providers\AppServiceProvider`:
- `TwoFactorAuthenticationServiceInterface` → `TwoFactorAuthenticationService`
- `PagesServiceContract` → `PagesService`

(See note in Section 11: 2FA service implementation placeholder currently empty.)

## 5. Backend Domains Overview
### Content Management
| Entity | Notes |
|--------|-------|
| Post | Rich publishing model with media, revisions (placeholder), taxonomy relationships, aggregate conversion & versioning fields. |
| Page | Simple static content pages CRUD via `PagesService`. |
| Category | Hierarchical taxonomy (self-referencing parent/children), featured & ordering, path & depth helpers, media support. |
| Tag | Lightweight labeling with post counts (controller pagination). |

### Security
- Two‑factor enrollment, status, recovery codes (routes under `/two-factor` + settings user nested routes).
- Roles & permissions management endpoints under `settings/permissions` (leveraging spatie/permission).

### Settings
- User profile, password, and 2FA management screens (nested under `settings/users/{id}`) using Inertia views.
- Site settings placeholder (`settings/site`).

## 6. Routes Summary
Middleware groups use `auth`, `verified`, and relevant throttling. Below is an abridged list (see `routes/*.php` for full definitions):

Public / Auth:
- `GET /` (home) → Inertia `Welcome`
- `GET /dashboard` (auth, verified) → `Dashboard`
- Registration / Login / Password Reset / Email Verification via Fortify controllers (guest / auth groups)
- `GET /check-email` / `GET /check-username` (AJAX helpers)

Two-Factor API (authenticated):
- `GET /two-factor/status`
- `GET /two-factor/setup`
- `POST /two-factor/enable`
- `DELETE /two-factor/disable`
- Recovery codes: list, download, regenerate (two-step), consume

Admin (`/admin`, auth+verified):
- `/admin/dashboard`
- Posts: index, create, edit, show
- Categories: index, create, edit
- Tags: index, create, edit
- Pages: full CRUD (index, create, store, edit, update, destroy)
- Settings sub-routes included via `settings.php`

Settings (`/settings` prefix):
- Redirect root → `settings/site`
- Users: nested id → profile, password, two-factor management
- Permissions: roles CRUD endpoints

## 7. Models (Selected Details)
### Post
- Fillable includes metadata (SEO, Open Graph, Twitter), versioning, relationships, counters, structured data.
- Casts for arrays and ints; activity logging logs all attributes (dirty-only).
- Media Collections: `featured_images`, `gallery`, `attachments`.
- Scopes: `published`, `byType`.
- Domain methods bridge to `PostAggregate` (aggregate file not shown in current excerpt—expand to view if needed).

### Category
- Hierarchical: `parent`, `children`, recursive traversal via `getAllChildren()`.
- Accessors: `full_path`, `depth` (naming uses `getFullPathAttribute()` -> `full_path`).
- Scope helpers for featured & ordering.

### Page
- Minimal static content: `title`, `slug`, `content`, `published`.

### Tag
- (Model not shown in gathered excerpt but controller references typical `name`, `slug`, relation `posts`).

## 8. Services
### PagesService
Interface (`PagesServiceContract`) + concrete implementation handling basic CRUD calls, bound as singleton. This abstraction allows injecting domain logic (events, caching, versioning) later without changing controllers.

### TwoFactorAuthenticationService
Interface is registered, but the implementation file located at `app/Domains/Security/Services/TwoFactorAuthenticationService.php` is currently empty (placeholder). Needs methods for:
- secret key generation
- provisioning URI / QR code
- enabling/disabling flow
- recovery code lifecycle
- validation of tokens

## 9. Frontend Stack
- Inertia pages live in `resources/js/Pages` (not expanded here, but routes map to names like `admin/posts/Index`).
- Tailwind 4 + PostCSS config + class variance utility helpers (`class-variance-authority`, `tailwind-merge`).
- Icon strategy migrating from `lucide-vue-next` to Iconify per TODO.
- Vite handles both client & optional SSR build (`build:ssr` script).

## 10. Tooling & Scripts
Composer scripts:
- `composer dev` launches PHP server, queue listener, pail log viewer, and Vite concurrently.
- `composer dev:ssr` adds Inertia SSR server.
- `composer test` clears config then runs tests.

NPM scripts:
- `npm run dev` – Vite dev server
- `npm run build` / `build:ssr`
- `npm run lint` – ESLint with auto-fix
- `npm run format` / `format:check` – Prettier
- `npm run test:e2e` – Playwright tests

## 11. Current Gaps / TODO
Original `TODO.txt` items:
1. Replace API with Inertia (progress appears partial—some controllers return Inertia already).
2. Update all stores to make Inertia calls (state stores likely still using previous API pattern).
3. Remove lucide icons and replace with Iconify + Material Light set.
4. Sidebar nav: expandable parent items controlling visibility of children.

Additional suggested next steps:
- Implement `TwoFactorAuthenticationService` logic + tests.
- Add validation & store/update endpoints for Tags & Categories (only index/create/edit views wired currently; no persistence endpoints in provided excerpts).
- Introduce Policies or Gates for admin routes (roles/permissions integration).
- Add dedicated DTO / Resource transformers for Post & Category to avoid ad-hoc arrays in controllers.
- Add missing migration files for taxonomy pivot tables (`post_categories`, `post_tags`) if not present.
- Provide seeders for sample content & roles.
- Add caching (e.g., tagged cache) for navigation / category trees.
- Expand activity log scoping for selective attributes and redact sensitive fields.

## 12. Setup & Installation
Prerequisites: PHP 8.4+, Composer, Node 20+, SQLite (or MySQL if reconfigured), Redis (optional), pnpm/npm.

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy and adjust environment
cp .env.example .env
php artisan key:generate

# 3. (Optional) Switch DB if not using SQLite
# Update DB_* vars in .env

# 4. Run migrations
php artisan migrate

# 5. Install JS dependencies
npm install

# 6. Run dev environment (PHP server + queue + logs + Vite)
composer dev
```

Visit: http://localhost:8000 (if artisan serve) or as configured.

## 13. Testing
### Backend
```bash
php artisan test   # or: ./vendor/bin/pest
```
Add Pest tests in `tests/Feature` and `tests/Unit`. Include factories for domain models (Page, Post, Category, Tag).

### E2E (Playwright)
```bash
npx playwright install --with-deps
npm run test:e2e
```
Configure authenticated flows by seeding a test user & using storage state.

## 14. Code Quality
- PHP: Laravel Pint (run `./vendor/bin/pint` or add script) – not explicitly scripted yet.
- JS/TS: ESLint + Prettier integrated; run `npm run lint` & `npm run format`.
- Type checking: `vue-tsc --noEmit` can be added to CI.

## 15. Environment / Configuration Notes
Key `.env` toggles:
- `SESSION_DRIVER=database` for persistent sessions.
- `QUEUE_CONNECTION=database` (consider Redis for production + Horizon dashboard).
- `LOG_CHANNEL=stack` with simplified stack (single).
- `BCRYPT_ROUNDS` can be tuned down in local for speed.

## 16. Architectural Principles
- Separation of Concerns: Domain-specific logic not mixed into generic `App\Http\Controllers` when specialized (e.g., ContentManagement domain dedicated controllers & services).
- Incremental Domain Layer: Services introduced only where abstraction adds value (e.g., `PagesService` vs direct model inline logic elsewhere).
- Extensibility: Service interfaces allow feature growth (e.g., caching pages, event sourcing posts) without controller rewrites.

## 17. Adding a New Domain (Example)
1. Create directory `app/Domains/YourDomain`.
2. Add subfolders: `Models`, `Http/Controllers`, `Services`, `Policies`, etc.
3. Define service contracts in `Services` and bind in a service provider (either existing `AppServiceProvider` or a new domain provider registered in `config/app.php`).
4. Add migrations & factories.
5. Create routes in a new `routes/*.php` file and include from `web.php` or `admin.php`.
6. Build Inertia pages under `resources/js/Pages/{your-domain}`.

## 18. Security Considerations
- Ensure enabling/disabling 2FA requires password confirmation (not shown; consider adding).
- Rate limit sensitive endpoints (2FA, password updates) beyond current global throttles.
- Implement content authorization (Policies for Posts, Pages, Taxonomy) before multi-role deployment.
- Validate slug uniqueness robustly (currently basic rule in PagesController; similar needed for categories/tags).

## 19. Deployment Notes
- Build assets: `npm run build` (and `npm run build:ssr` if SSR enabled).
- Optimize config/routes: `php artisan config:cache && php artisan route:cache` (avoid during local dev).
- Queue workers: Use Horizon or `php artisan queue:work` supervised.
- Media storage: Configure `FILESYSTEM_DISK` (S3, etc.) for production; run `php artisan storage:link` if using local/public.

## 20. Glossary
| Term | Description |
|------|-------------|
| Inertia Response | Server-side controller returning a Vue page component name + props; replaces separate API calls. |
| Aggregate | An object representing cohesive domain state snapshot (e.g., `PostAggregate`). |
| Recovery Codes | One-time use backup codes for 2FA access if TOTP device unavailable. |

## 21. Status Summary
| Area | Status |
|------|--------|
| Pages CRUD | Implemented (service-backed) |
| Posts Admin | List/create/edit views (no persistence logic shown) |
| Taxonomy | Listing & skeleton create/edit views; missing store/update/delete endpoints in excerpts |
| Two-Factor | Routes + controllers; service implementation pending |
| Roles/Permissions | Basic CRUD endpoints (Roles) present |
| Tests | Framework present; coverage unknown |
| E2E | Playwright configured; tests not enumerated |

## 22. License
MIT (per `composer.json`).

---
Feel free to extend this README and split deeper technical details into `docs/` (e.g., `ARCHITECTURE.md`, `SECURITY.md`) as the codebase grows.

