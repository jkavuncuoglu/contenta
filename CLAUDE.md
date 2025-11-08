# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Contenta is a Laravel 12 + Vue 3 (Inertia.js) application for content-centric platforms (CMS/knowledge base/publishing) using domain-driven architecture with opinionated separation under `app/Domains/*`.

## Commands

### Development
```bash
# Start development server (PHP server + queue + logs + Vite concurrently)
composer dev

# Start development with SSR
composer dev:ssr

# Frontend only
npm run dev
```

### Building
```bash
# Build frontend assets
npm run build

# Build with SSR support
npm run build:ssr
```

### Testing
```bash
# Run PHP tests (uses Pest)
composer test
# or
./vendor/bin/pest

# Run E2E tests (Playwright)
npm run test:e2e
```

### Code Quality
```bash
# PHP formatting (Laravel Pint)
./vendor/bin/pint

# Frontend linting and formatting
npm run lint
npm run format
npm run format:check
```

### Database
```bash
# Run migrations
php artisan migrate

# Clear caches and optimize
php artisan optimize:clear
composer dump-autoload
```

## Architecture

### Domain-Driven Structure
The application uses domain separation under `app/Domains/`:

- **ContentManagement/**: Posts, Pages, Categories, Tags, Comments
- **Media/**: File uploads and media management
- **Security/**: API tokens, two-factor auth, roles/permissions
- **Settings/**: User profile, site configuration

Each domain encapsulates:
- Models/Aggregates
- Http/Controllers (UI + Admin endpoints)
- Services (application layer)
- Policies, Jobs, Events (future)

### Service Bindings
Key services registered in `App\Providers\AppServiceProvider`:
- `TwoFactorAuthenticationServiceInterface` → `TwoFactorAuthenticationService`
- `PagesServiceContract` → `PagesService`
- `MediaServiceContract` → `MediaService`
- `CommentsServiceContract` → `CommentsService`

### Frontend Structure
```
resources/js/
├── pages/          # Inertia page components
├── layouts/        # Application layouts
├── components/     # Reusable Vue components
├── stores/         # Application state
├── types/          # TypeScript definitions
└── lib/            # Utilities and helpers
```

## Key Technologies

### Backend
- Laravel 12 (PHP 8.4)
- Laravel Fortify (authentication)
- Laravel Sanctum (API tokens)
- Spatie packages (permissions, media, activity log)
- Horizon (queue management)

### Frontend
- Vue 3 + TypeScript
- Inertia.js (SPA-like experience)
- Tailwind CSS v4
- Reka UI components
- Vite 7 (build tool)

### Testing
- Pest (PHP testing)
- Playwright (E2E testing)

## Route Structure

- `/` - Public pages
- `/dashboard` - Authenticated dashboard
- `/admin/*` - Admin content management
  - `/admin/posts` - Posts management
  - `/admin/page-builder` - PageBuilder (drag & drop page editor)
  - `/admin/categories` - Categories management
  - `/admin/tags` - Tags management
  - `/admin/comments` - Comments moderation
  - `/admin/media` - Media library
- `/settings/*` - User/site settings
- `/user/settings/*` - User profile management
- `/two-factor/*` - 2FA endpoints

## Development Workflow

### Running Tests
Always run tests before committing changes:
```bash
composer test
npm run test:e2e  # if E2E tests exist
```

### Code Style
The project enforces code style through:
- Laravel Pint for PHP (configured in CI)
- ESLint + Prettier for TypeScript/Vue
- GitHub Actions workflows for automatic checks

### Making Changes
1. Follow existing domain patterns when adding features
2. Use service interfaces for domain logic abstraction
3. Write tests for new functionality
4. Ensure code passes linting before committing

## Important Notes

### Environment Setup
- Uses SQLite by default (database/database.sqlite)
- Queue connection: database (consider Redis for production)
- Session driver: database for persistence

### Security Features
- API token management with granular abilities
- Two-factor authentication (TOTP + recovery codes)
- Role-based permissions via Spatie
- Activity logging for audit trails

### Current Limitations
- `TwoFactorAuthenticationService` implementation is placeholder
- Some taxonomy endpoints missing store/update logic
- Icon system migrating from Lucide to Iconify (per TODO.txt)

### Media Handling
Uses Spatie Media Library with collections:
- `featured_images`
- `gallery`
- `attachments`

Run `php artisan storage:link` for local file serving.

## Service Container Usage

When adding new services:
1. Create interface in domain's `Services/` directory
2. Implement concrete class
3. Register binding in `AppServiceProvider`
4. Type-hint interface in controllers

## Database Conventions

- Uses Laravel's standard migration structure
- Activity logging tracks all model changes
- Soft deletes where appropriate
- Uses UUIDs for API tokens via Sanctum