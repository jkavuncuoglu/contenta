# Features Overview

[← Back: Setup](SETUP.md) • [Read next: Backend Guide →](BACKEND.md)

## Security Keys (WebAuthn)

- Register platform or cross-platform authenticators
- Manage multiple credentials (rename, delete)
- Track creation and last-used timestamps
- CSRF-protected flows with server-side validation

UI entry point:
- Page: `/resources/js/pages/settings/Security.vue`
- Manager component: `/resources/js/components/WebAuthnManager.vue`

## Developer Experience

- Laravel backend with migrations, controllers, and config
- Vue + TypeScript frontend, composables, and single-file components
- npm-based build tooling (Vite) and Composer-managed PHP packages

---
Navigation • [Index](INDEX.md) • [Previous: Setup](SETUP.md) • [Next: Backend Guide →](BACKEND.md) • [Main README](../../README.md)
