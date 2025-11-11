# Backend Guide (Laravel)

## Dependencies

- WebAuthn: `laragear/webauthn` (v4.0.1)
- Config: `config/webauthn.php`

## Data

- Migration for credentials:
  `database/migrations/2025_10_25_180121_create_webauthn_credentials.php`

## Models

- `App\Models\User` uses the trait:
  `Laragear\WebAuthn\WebAuthnAuthentication`

## Routes

Defined in `routes/webauthn.php` (authenticated):

- POST `/webauthn/register/options` — get registration options
- POST `/webauthn/register` — register a new credential
- GET `/webauthn/credentials` — list credentials
- PATCH `/webauthn/credentials/{id}` — update credential name
- DELETE `/webauthn/credentials/{id}` — delete credential

## Controllers

- `app/Http/Controllers/WebAuthnController.php` handles the flows:
  - options, registration, listing, rename, delete

## Security

- CSRF protection enabled via Laravel middleware
- Server-side validation of WebAuthn payloads
- Unique credential IDs and last-used tracking

## Local Tools

- Run tests:
  php artisan test
- Clear caches (if needed):
  php artisan optimize:clear
