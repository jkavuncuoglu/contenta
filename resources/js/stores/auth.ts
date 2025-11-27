import { ref } from 'vue';

const user = ref<any>(
    typeof window !== 'undefined'
        ? ((window as any).__page?.props?.user ?? null)
        : null,
);
const error = ref<string | null>(null);

export function useAuthStore() {
    const clearError = () => (error.value = null);
    const setError = (msg?: string | null) => (error.value = msg ?? null);

    // Minimal implementations that attempt to call backend endpoints when available
    const fetchUser = async () => {
        try {
            const res = await fetch('/user', { credentials: 'same-origin' });
            if (!res.ok) return { success: false };
            const data = await res.json();
            user.value = data.data ?? data ?? null;
            return { success: true, data: user.value };
        } catch (e) {
            return { success: false, error: e };
        }
    };

    const getTwoFactorRecoveryCodes = async () => {
        try {
            const res = await fetch('/auth/two-factor/recovery-codes', {
                credentials: 'same-origin',
            });
            if (!res.ok) return { success: false };
            const data = await res.json();
            return { success: true, data: data.data ?? data };
        } catch (e) {
            return { success: false, error: e };
        }
    };

    const getTwoFactorQrCode = async () => {
        try {
            const res = await fetch('/auth/two-factor/qrcode', {
                credentials: 'same-origin',
            });
            if (!res.ok) return { success: false };
            const data = await res.json();
            return { success: true, svg: data.svg ?? data.data?.svg ?? null };
        } catch (e) {
            return { success: false, error: e };
        }
    };

    const enableTwoFactor = async () => {
        try {
            const res = await fetch('/auth/two-factor/enable', {
                method: 'POST',
                credentials: 'same-origin',
            });
            if (!res.ok) return { success: false };
            const data = await res.json();
            return { success: true, data };
        } catch (e) {
            return { success: false, error: e };
        }
    };

    const confirmTwoFactor = async (code: string) => {
        try {
            const res = await fetch('/auth/two-factor/confirm', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ code }),
            });
            if (!res.ok) return { success: false };
            const data = await res.json();
            return { success: true, data };
        } catch (e) {
            return { success: false, error: e };
        }
    };

    const regenerateTwoFactorRecoveryCodes = async () => {
        try {
            const res = await fetch(
                '/auth/two-factor/recovery-codes/regenerate',
                { method: 'POST', credentials: 'same-origin' },
            );
            if (!res.ok) return { success: false };
            const data = await res.json();
            return { success: true, data: data.data ?? data };
        } catch (e) {
            return { success: false, error: e };
        }
    };

    const disableTwoFactor = async () => {
        try {
            const res = await fetch('/auth/two-factor/disable', {
                method: 'POST',
                credentials: 'same-origin',
            });
            if (!res.ok) return { success: false };
            const data = await res.json();
            return { success: true, data };
        } catch (e) {
            return { success: false, error: e };
        }
    };

    return {
        user,
        error,
        clearError,
        setError,
        fetchUser,
        getTwoFactorRecoveryCodes,
        getTwoFactorQrCode,
        enableTwoFactor,
        confirmTwoFactor,
        regenerateTwoFactorRecoveryCodes,
        disableTwoFactor,
    };
}

export default useAuthStore;
