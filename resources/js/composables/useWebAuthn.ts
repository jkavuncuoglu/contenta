import { ref } from 'vue';
import { startRegistration } from '@simplewebauthn/browser';
import { router } from '@inertiajs/vue3';

interface WebAuthnCredential {
    id: string;
    name: string;
    created_at: string;
    last_used_at: string;
}

// Helper to get CSRF token
const getCsrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
};

export function useWebAuthn() {
    const credentials = ref<WebAuthnCredential[]>([]);
    const isLoading = ref(false);
    const errors = ref<string[]>([]);

    const fetchCredentials = async () => {
        try {
            isLoading.value = true;
            errors.value = [];

            const response = await fetch('/webauthn/credentials', {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch credentials');
            }

            const data = await response.json();
            credentials.value = data.credentials;
        } catch (error: any) {
            errors.value.push(error.message || 'Failed to fetch credentials');
        } finally {
            isLoading.value = false;
        }
    };

    const registerCredential = async (name?: string) => {
        try {
            isLoading.value = true;
            errors.value = [];

            // Get registration options from the server
            const optionsResponse = await fetch('/webauthn/register/options', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });

            if (!optionsResponse.ok) {
                throw new Error('Failed to get registration options');
            }

            const options = await optionsResponse.json();

            // Start the WebAuthn registration ceremony
            const credential = await startRegistration(options);

            // Send the credential to the server
            const registerResponse = await fetch('/webauthn/register', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    ...credential,
                    name: name || 'Security Key',
                }),
            });

            if (!registerResponse.ok) {
                throw new Error('Failed to register credential');
            }

            const result = await registerResponse.json();

            if (result.success) {
                await fetchCredentials();
                return { success: true, credential: result.credential };
            }

            throw new Error('Registration failed');
        } catch (error: any) {
            if (error.name === 'NotAllowedError') {
                errors.value.push('Registration was cancelled or timed out');
            } else if (error.name === 'NotSupportedError') {
                errors.value.push('WebAuthn is not supported by your browser');
            } else {
                errors.value.push(error.message || 'Failed to register credential');
            }
            return { success: false };
        } finally {
            isLoading.value = false;
        }
    };

    const updateCredential = async (id: string, name: string) => {
        try {
            isLoading.value = true;
            errors.value = [];

            const response = await fetch(`/webauthn/credentials/${id}`, {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ name }),
            });

            if (!response.ok) {
                throw new Error('Failed to update credential');
            }

            const result = await response.json();

            if (result.success) {
                await fetchCredentials();
                return { success: true };
            }

            throw new Error('Update failed');
        } catch (error: any) {
            errors.value.push(error.message || 'Failed to update credential');
            return { success: false };
        } finally {
            isLoading.value = false;
        }
    };

    const deleteCredential = async (id: string) => {
        try {
            isLoading.value = true;
            errors.value = [];

            const response = await fetch(`/webauthn/credentials/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });

            if (!response.ok) {
                throw new Error('Failed to delete credential');
            }

            const result = await response.json();

            if (result.success) {
                await fetchCredentials();
                return { success: true };
            }

            throw new Error('Delete failed');
        } catch (error: any) {
            errors.value.push(error.message || 'Failed to delete credential');
            return { success: false };
        } finally {
            isLoading.value = false;
        }
    };

    const clearErrors = () => {
        errors.value = [];
    };

    return {
        credentials,
        isLoading,
        errors,
        fetchCredentials,
        registerCredential,
        updateCredential,
        deleteCredential,
        clearErrors,
    };
}
