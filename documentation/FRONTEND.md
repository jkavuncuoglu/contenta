# Frontend Guide (Vue + TypeScript)

## Dependencies

- WebAuthn client: `@simplewebauthn/browser` (^13.2.2)

## Key Files

- Composable: `/resources/js/composables/useWebAuthn.ts`
  - `fetchCredentials()`
  - `registerCredential(name)`
  - `updateCredential(id, name)`
  - `deleteCredential(id)`
- Component: `/resources/js/components/WebAuthnManager.vue`
- Page integration: `/resources/js/pages/settings/Security.vue`

## Dev Commands

- Install deps:
  npm install
- Dev server:
  npm run dev
- Production build:
  npm run build

## Notes

- WebAuthn flows require a secure origin in production (HTTPS).
- The composable handles loading and error states and communicates with authenticated backend routes.
