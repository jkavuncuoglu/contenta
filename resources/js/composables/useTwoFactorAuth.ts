import { ref, computed } from 'vue';

interface SetupData {
    qrCode: string; // base64
    manualEntry: string;
}

interface EnableResponse {
    success: boolean;
    message: string;
    recovery_codes?: string[];
    errors?: string[];
}

interface RecoveryCodesResponse {
    recovery_codes: string[];
    has_viewed: boolean;
    show_warning: boolean;
    available_count: number;
}

interface RegenerationResponse {
    success: boolean;
    message: string;
    recovery_codes?: string[];
    errors?: string[];
}

const setupData = ref<SetupData | null>(null);
const recoveryCodesList = ref<string[]>([]);
const recoveryCodesDownloaded = ref(false);
const recoveryCodesWarning = ref(false);
const hasViewedCodes = ref(false);
const isEnabling = ref(false);
const isLoadingSetup = ref(false);
const isRequestingRegeneration = ref(false);
const isConfirmingRegeneration = ref(false);
const errors = ref<string[]>([]);

const fetchJson = async <T>(input: RequestInfo, init: RequestInit = {}): Promise<T> => {
    const resp = await fetch(input, {
        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        ...init,
    });
    if (!resp.ok) {
        let body: any = null;
        try { body = await resp.json(); } catch {}
        throw new Error(body?.message || `HTTP ${resp.status}`);
    }
    return resp.json();
};

const postJson = async <T>(url: string, payload: any): Promise<T> => {
    return fetchJson<T>(url, { method: 'POST', body: JSON.stringify(payload) });
};

const hasSetupData = computed(() => !!setupData.value);

export const useTwoFactorAuth = () => {
    const clearErrors = () => { errors.value = []; };

    const fetchSetupData = async () => {
        try {
            clearErrors();
            isLoadingSetup.value = true;
            const data = await fetchJson<SetupData>('/two-factor/setup');
            setupData.value = data;
        } catch (e: any) {
            errors.value.push(e.message || 'Failed to load setup data');
        } finally {
            isLoadingSetup.value = false;
        }
    };

    const validateTwoFactorCode = async (code: string): Promise<EnableResponse | null> => {
        if (!code || code.length !== 6) {
            errors.value.push('Enter a valid 6-digit code');
            return null;
        }
        try {
            clearErrors();
            isEnabling.value = true;
            const resp = await postJson<EnableResponse>('/two-factor/enable', { code });
            if (resp.success && resp.recovery_codes) {
                recoveryCodesList.value = resp.recovery_codes;
                hasViewedCodes.value = false;
            } else if (!resp.success && resp.errors) {
                errors.value.push(...resp.errors);
            }
            return resp;
        } catch (e: any) {
            errors.value.push(e.message || 'Failed to enable 2FA');
            return null;
        } finally {
            isEnabling.value = false;
        }
    };

    const fetchRecoveryCodes = async () => {
        try {
            clearErrors();
            const resp = await fetchJson<RecoveryCodesResponse>('/two-factor/recovery-codes');
            recoveryCodesList.value = resp.recovery_codes || [];
            hasViewedCodes.value = resp.has_viewed;
            recoveryCodesWarning.value = resp.show_warning;
        } catch (e: any) {
            errors.value.push(e.message || 'Failed to fetch recovery codes');
        }
    };

    const downloadCodes = async () => {
        try {
            clearErrors();
            // Using normal navigation so browser downloads file; only if not already viewed
            if (hasViewedCodes.value) {
                errors.value.push('Codes already viewed. Regenerate to download again.');
                return;
            }
            const link = document.createElement('a');
            link.href = '/two-factor/recovery-codes/download';
            link.download = 'recovery-codes.txt';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            recoveryCodesDownloaded.value = true;
            // Mark as viewed by also calling fetch to update state (will now be empty on subsequent fetch)
            setTimeout(fetchRecoveryCodes, 800);
        } catch (e: any) {
            errors.value.push(e.message || 'Failed to download recovery codes');
        }
    };

    const regenerateCodes = async (password: string, code: string): Promise<RegenerationResponse | null> => {
        if (!password || password.length < 8) {
            errors.value.push('Password required (min 8 chars)');
            return null;
        }
        if (!code || code.length !== 6) {
            errors.value.push('Valid 6-digit 2FA code required');
            return null;
        }
        try {
            clearErrors();
            isRequestingRegeneration.value = true;
            const resp = await postJson<RegenerationResponse>('/two-factor/recovery-codes/regenerate', { password, code });
            if (!resp.success && resp.errors) {
                errors.value.push(...resp.errors);
            }
            return resp;
        } catch (e: any) {
            errors.value.push(e.message || 'Failed to request regeneration');
            return null;
        } finally {
            isRequestingRegeneration.value = false;
        }
    };

    const confirmCodeRegeneration = async (token?: string) => {
        try {
            clearErrors();
            isConfirmingRegeneration.value = true;
            const url = token ? `/two-factor/recovery-codes/confirm?token=${encodeURIComponent(token)}` : '/two-factor/recovery-codes/confirm';
            const resp = await fetchJson<RegenerationResponse>(url);
            if (resp.success && resp.recovery_codes) {
                recoveryCodesList.value = resp.recovery_codes;
                hasViewedCodes.value = false; // not yet marked viewed until user downloads or retrieval endpoint called
            } else if (!resp.success && resp.errors) {
                errors.value.push(...resp.errors);
            }
            return resp;
        } catch (e: any) {
            errors.value.push(e.message || 'Failed to confirm regeneration');
            return null;
        } finally {
            isConfirmingRegeneration.value = false;
        }
    };

    const clearSetupData = () => { setupData.value = null; };
    const clearTwoFactorAuthData = () => {
        clearSetupData();
        recoveryCodesList.value = [];
        recoveryCodesDownloaded.value = false;
        recoveryCodesWarning.value = false;
        hasViewedCodes.value = false;
        clearErrors();
    };

    return {
        // state
        setupData,
        recoveryCodesList,
        recoveryCodesDownloaded,
        recoveryCodesWarning,
        hasViewedCodes,
        isEnabling,
        isLoadingSetup,
        isRequestingRegeneration,
        isConfirmingRegeneration,
        errors,
        hasSetupData,
        // actions
        fetchSetupData,
        validateTwoFactorCode,
        fetchRecoveryCodes,
        downloadCodes,
        regenerateCodes,
        confirmCodeRegeneration,
        clearSetupData,
        clearTwoFactorAuthData,
        clearErrors,
    };
};
