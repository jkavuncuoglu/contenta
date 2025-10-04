// NOTE: This file used to provide a fetch-based shim for axios; it has been replaced
// with an Inertia-backed implementation in `resources/js/lib/api.ts`.
// We re-export `api` here for backwards compatibility so imports of '@/plugins/axios'
// keep working while the codebase migrates fully to Inertia.

export { api } from '@/lib/api';
export default api;
