# Setup and Local Development

[← Back: Index](INDEX.md) • [Read next: Features Overview →](FEATURES.md)

## Prerequisites

- PHP and Composer
- Node.js (LTS) and npm
- A SQL database (MySQL/MariaDB, PostgreSQL, or SQLite)
- OpenSSL and PHP extensions commonly required by Laravel

## Clone or Fork

- Fork on GitHub and clone your fork, or clone this repository directly.
- Alternatively download a prepared release build: see RELEASES.md

## Environment

1. Copy the example environment:
   cp .env.example .env
2. Set database credentials and `APP_URL`.
3. Generate app key:
   php artisan key:generate

## Install Dependencies

- Backend:
  composer install
- Frontend:
  npm install

## Database

- Run migrations:
  php artisan migrate
- Optional: add seeders if available:
  php artisan db:seed

## WebAuthn Configuration

- The package `laragear/webauthn` is configured in `config/webauthn.php`.
- Ensure `APP_URL` reflects your local origin.
- Use HTTPS in production for WebAuthn. Localhost is allowed by most browsers.

## Run the App

- Start backend:
  php artisan serve
- Start frontend (Vite dev server):
  npm run dev

Visit the app in your browser using the URL shown above.

## Verify WebAuthn

- Go to `/settings/security`
- Use the "Security Key (U2F)" section to register and manage keys.

---
Navigation • [Index](INDEX.md) • [Previous: Index](INDEX.md) • [Next: Features Overview →](FEATURES.md) • [Main README](../../README.md)
