# Security Overview

## WebAuthn (Security Keys / U2F)

- Strong second factor using platform or roaming authenticators
- Credential registration options are fetched server-side
- Browser prompts user interaction for attestation
- Credentials are verified and stored server-side
- Each credential has a unique ID with creation and last-used timestamps

Details: `WEBAUTHN_IMPLEMENTATION.md`

## Request Protection

- All requests use CSRF tokens
- Server-side validation of payloads and identifiers

## Operational Guidance

- Enforce HTTPS in production
- Set secure cookies and appropriate `SameSite` attributes
- Keep `APP_URL` accurate for correct origin checks
- Rotate secrets and keep dependencies updated

## Reporting Vulnerabilities

- Prefer private disclosure through maintainers
- If unavailable, open a security advisory or a private issue
