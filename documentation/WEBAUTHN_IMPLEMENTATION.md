# WebAuthn Implementation

[← Back: Security Overview](SECURITY.md) • [Read next: Two-Factor Implementation →](TWO_FACTOR_IMPLEMENTATION.md)

This document describes the WebAuthn (Security Keys / U2F) implementation for two-factor authentication.

## Overview

WebAuthn has been integrated to allow users to register and use physical security keys (like YubiKey) or platform authenticators (like Touch ID, Face ID, Windows Hello) for authentication.

## Installation

The following packages were installed:

### Backend
- `laragear/webauthn` (v4.0.1) - Laravel WebAuthn implementation

### Frontend
- `@simplewebauthn/browser` (^13.2.2) - Browser client for WebAuthn

## Configuration

### Database Migration

A migration was created to store WebAuthn credentials:
- `/database/migrations/2025_10_25_180121_create_webauthn_credentials.php`

The migration has been run successfully.

### Configuration File

- `/config/webauthn.php` - WebAuthn configuration (published from package)

### User Model

The `WebAuthnAuthentication` trait has been added to the User model:

```php
use Laragear\WebAuthn\WebAuthnAuthentication;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, WebAuthnAuthentication;
}
```

## Routes

WebAuthn routes are defined in `/routes/webauthn.php`:

- `POST /webauthn/register/options` - Get registration options
- `POST /webauthn/register` - Register a new credential
- `GET /webauthn/credentials` - List all credentials
- `PATCH /webauthn/credentials/{id}` - Update credential name
- `DELETE /webauthn/credentials/{id}` - Delete a credential

All routes require authentication.

## Backend Components

### Controller

`/app/Http/Controllers/WebAuthnController.php`

Handles all WebAuthn operations:
- Getting registration options
- Registering new credentials
- Listing credentials
- Updating credential names
- Deleting credentials

## Frontend Components

### Composable

`/resources/js/composables/useWebAuthn.ts`

A Vue composable that provides:
- `fetchCredentials()` - Fetch user's registered credentials
- `registerCredential(name)` - Register a new security key
- `updateCredential(id, name)` - Update credential name
- `deleteCredential(id)` - Remove a credential
- State management for credentials, loading states, and errors

### Vue Component

`/resources/js/components/WebAuthnManager.vue`

A complete UI for managing security keys featuring:
- List of registered credentials with creation and last used dates
- Add new security key with custom name
- Edit credential names inline
- Delete credentials with confirmation
- Empty state when no keys are registered
- Error handling and user feedback
- Loading states during operations

## User Interface

The WebAuthn manager is integrated into the Security Settings page at:
`/resources/js/pages/settings/Security.vue`

It appears in a section titled "Security Key (U2F)" below the Two-Factor Authentication section.

## Features

### Registration Flow

1. User clicks "Register Security Key" or "Add Key"
2. User enters an optional name for the key (e.g., "YubiKey 5C", "Touch ID")
3. System requests registration options from the server
4. Browser prompts user to interact with their security device
5. Credential is sent to server and stored
6. UI updates to show the new credential

### Management Features

- **View Credentials**: See all registered keys with metadata
- **Rename Keys**: Click edit icon to give keys memorable names
- **Delete Keys**: Remove keys with confirmation dialog
- **Multiple Keys**: Support for registering multiple security keys

### Security Features

- CSRF token protection on all requests
- Server-side validation of all credentials
- Unique identification per credential
- Last used timestamp tracking

## Browser Compatibility

WebAuthn is supported in:
- Chrome/Edge 67+
- Firefox 60+
- Safari 13+
- Opera 54+

The component gracefully handles browsers without WebAuthn support by showing an appropriate error message.

## Usage Example

Users can access the WebAuthn manager at:
```
/settings/security
```

The interface provides a user-friendly way to:
1. Register their first security key
2. Add additional backup keys
3. Manage existing keys
4. Remove compromised or lost keys

## Testing

To test the implementation:

1. Navigate to `/settings/security`
2. Scroll to "Security Key (U2F)" section
3. Click "Register Security Key"
4. Follow browser prompts to register a key
5. Test editing and deleting credentials

## Future Enhancements

Potential improvements:
- Use WebAuthn for passwordless login
- Add credential usage statistics
- Support for conditional UI based on available authenticators
- Integration with the login flow for 2FA
- Backup reminder if only one key is registered

## Troubleshooting

### Common Issues

1. **"WebAuthn is not supported"**: User's browser doesn't support WebAuthn
2. **"Registration was cancelled or timed out"**: User didn't complete the browser prompt
3. **CSRF token errors**: Page needs to be refreshed

### Debug Mode

Check browser console for detailed error messages during registration or authentication.

## References

- [WebAuthn Specification](https://www.w3.org/TR/webauthn/)
- [Laragear WebAuthn Documentation](https://github.com/Laragear/WebAuthn)
- [SimpleWebAuthn Documentation](https://simplewebauthn.dev/)

---
Navigation • [Index](INDEX.md) • [Previous: Security](SECURITY.md) • [Next: Two-Factor Implementation →](TWO_FACTOR_IMPLEMENTATION.md) • [Main README](../../README.md)
