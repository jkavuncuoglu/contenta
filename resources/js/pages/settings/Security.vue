<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import WebAuthnManager from '@/components/WebAuthnManager.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Icon } from '@iconify/vue';
import { Form, Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

interface Props {
    twoFactorEnabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    twoFactorEnabled: false,
});

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// Composable state
const {
    setupData,
    fetchSetupData,
    validateTwoFactorCode,
    recoveryCodesList,
    downloadCodes,
    recoveryCodesDownloaded,
    recoveryCodesWarning,
    regenerateCodes,
    confirmCodeRegeneration,
    errors,
    clearTwoFactorAuthData,
} = useTwoFactorAuth();

const enablingFlowStarted = ref(false);
const codeInput = ref('');
const passwordForRegeneration = ref('');
const twoFactorEnabled = ref<boolean>(props.twoFactorEnabled);
const showRecoveryCodes = ref(true);
const regenerationRequested = ref(false);
const awaitingRegenerationConfirmation = ref(false);

const canSubmitEnable = computed(
    () => codeInput.value.length === 6 && /^[0-9]{6}$/.test(codeInput.value),
);
const showEnableSection = computed(() => !twoFactorEnabled.value);

const startEnableFlow = async () => {
    errors.value.length = 0;
    enablingFlowStarted.value = true;
    await fetchSetupData();
};

const submitEnable = async () => {
    if (!canSubmitEnable.value) return;
    const resp = await validateTwoFactorCode(codeInput.value);
    if (resp && resp.success) {
        twoFactorEnabled.value = true;
        // codes already in recoveryCodesList
    }
};

const handleDisable = async () => {
    errors.value.length = 0;
    try {
        const tokenEl = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
        const csrf = tokenEl?.content || '';
        const resp = await fetch('/user/settings/security/two-factor/disable', {
            method: 'DELETE',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
            credentials: 'same-origin',
        });
        if (!resp.ok) {
            errors.value.push('Failed to disable 2FA');
            return;
        }
        twoFactorEnabled.value = false;
        clearTwoFactorAuthData();
        enablingFlowStarted.value = false;
        codeInput.value = '';
    } catch (e: any) {
        errors.value.push(e.message || 'Failed to disable 2FA');
    }
};

const handleRegenerationRequest = async () => {
    if (
        passwordForRegeneration.value.length < 8 ||
        codeInput.value.length !== 6
    ) {
        errors.value.push(
            'Password (min 8 chars) and valid 6-digit code required.',
        );
        return;
    }
    const resp = await regenerateCodes(
        passwordForRegeneration.value,
        codeInput.value,
    );
    if (resp && resp.success) {
        regenerationRequested.value = true;
        awaitingRegenerationConfirmation.value = true;
    }
};

const handleConfirmRegeneration = async () => {
    const resp = await confirmCodeRegeneration();
    if (resp && resp.success) {
        awaitingRegenerationConfirmation.value = false;
        regenerationRequested.value = false;
        // New codes now in recoveryCodesList (single view) until downloaded/viewed endpoint marks them.
    }
};

onMounted(() => {
    // Could fetch status endpoint here if needed for up-to-date state
});
</script>

<template>
        <Head title="Security" />
        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    title="Security Settings"
                    description="Manage your account's security settings"
                />

                <div
                    v-if="errors.length"
                    class="rounded border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive"
                >
                    <ul class="list-disc space-y-1 pl-4">
                        <li v-for="err in errors" :key="err">{{ err }}</li>
                    </ul>
                </div>

                <!-- PASSWORD UPDATE FORM -->
                <div class="mb-4 space-y-6 rounded bg-neutral-800/30 p-4">
                    <HeadingSmall
                        title="Update Password"
                        description="Ensure your account is using a long, random password to stay secure"
                    />

                    <Form
                        :form="passwordForm"
                        :options="{
                            preserveScroll: true,
                        }"
                        reset-on-success
                        :reset-on-error="[
                            'password',
                            'password_confirmation',
                            'current_password',
                        ]"
                        class="space-y-6"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <div class="grid gap-2">
                            <Label for="current_password"
                                >Current password</Label
                            >
                            <Input
                                id="current_password"
                                name="current_password"
                                type="password"
                                class="mt-1 block w-full"
                                autocomplete="current-password"
                                placeholder="Current password"
                                v-model="passwordForm.current_password"
                            />
                            <InputError :message="errors.current_password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password">New password</Label>
                            <Input
                                id="password"
                                name="password"
                                type="password"
                                class="mt-1 block w-full"
                                autocomplete="new-password"
                                placeholder="New password"
                                v-model="passwordForm.password"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation"
                                >Confirm password</Label
                            >
                            <Input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                autocomplete="new-password"
                                placeholder="Confirm password"
                                v-model="passwordForm.password_confirmation"
                            />
                            <InputError
                                :message="errors.password_confirmation"
                            />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button
                                :disabled="processing"
                                data-test="update-password-button"
                            >
                                Save password
                            </Button>

                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-show="recentlySuccessful"
                                    class="text-sm text-neutral-600"
                                >
                                    Saved.
                                </p>
                            </Transition>
                        </div>
                    </Form>
                </div>

                <div class="mb-4 space-y-6 rounded bg-neutral-800/30 p-4">
                    <HeadingSmall
                        title="Two Factor Authentication"
                        description="Add additional security to your account using two-factor authentication."
                    />

                    <!-- ENABLE FLOW -->
                    <div
                        v-if="showEnableSection"
                        class="mb-4 flex flex-col space-y-4"
                    >
                        <Badge variant="destructive">Disabled</Badge>
                        <p class="text-sm text-muted-foreground">
                            Enable two-factor authentication to add an extra
                            layer of security to your account. You'll scan a QR
                            code or enter a setup key into an authenticator app
                            (Google Authenticator, 1Password, Authy, etc.).
                        </p>

                        <div v-if="!enablingFlowStarted">
                            <Button @click="startEnableFlow"
                                ><Icon
                                    icon="material-symbols-light:shield-lock"
                                    class="mr-1"
                                />
                                Enable 2FA</Button
                            >
                        </div>

                        <div v-else class="space-y-6 rounded border p-4">
                            <h3 class="text-sm font-semibold tracking-wide">
                                Step 1: Scan QR Code or Enter Key
                            </h3>
                            <div
                                v-if="setupData"
                                class="flex flex-col items-center space-y-4"
                            >
                                <img
                                    :src="
                                        'data:image/png;base64,' +
                                        setupData.qrCode
                                    "
                                    alt="QR Code"
                                    class="h-48 w-48"
                                />
                                <div
                                    class="rounded bg-muted px-2 py-1 font-mono text-xs break-all"
                                >
                                    {{ setupData.manualEntry }}
                                </div>
                                <p
                                    class="max-w-sm text-center text-xs text-muted-foreground"
                                >
                                    If you cannot scan the QR code, enter the
                                    key above manually in your authenticator
                                    app.
                                </p>
                            </div>
                            <div v-else class="text-sm text-muted-foreground">
                                Generating secret...
                            </div>

                            <div class="space-y-2">
                                <h3 class="text-sm font-semibold tracking-wide">
                                    Step 2: Enter 6-digit Code
                                </h3>
                                <input
                                    v-model="codeInput"
                                    maxlength="6"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    class="w-32 rounded border px-2 py-1 text-center font-mono text-lg tracking-widest"
                                    placeholder="000000"
                                />
                                <div>
                                    <Button
                                        :disabled="!canSubmitEnable"
                                        @click="submitEnable"
                                        >Verify & Enable</Button
                                    >
                                    <Button
                                        variant="secondary"
                                        class="ml-2"
                                        @click="
                                            clearTwoFactorAuthData();
                                            enablingFlowStarted = false;
                                            codeInput = '';
                                        "
                                        >Cancel</Button
                                    >
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Your authenticator app will generate a new
                                    code every 30 seconds.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ENABLED STATE -->
                    <div v-else class="flex flex-col space-y-4">
                        <Badge variant="default">Enabled</Badge>
                        <p class="text-sm text-muted-foreground">
                            Two-factor authentication is enabled. Use your
                            authenticator app during login. Recovery codes are a
                            fallback if you lose access to the app.
                        </p>

                        <!-- Recovery Codes Display (first view or immediately after regeneration) -->
                        <div
                            v-if="recoveryCodesList.length && showRecoveryCodes"
                            class="space-y-3 rounded border p-4"
                        >
                            <h3 class="text-sm font-semibold">
                                Recovery Codes (Store Securely)
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                These codes are only shown once. Save them in a
                                secure password manager. Each can be used one
                                time.
                            </p>
                            <ul class="grid grid-cols-2 gap-2">
                                <li
                                    v-for="code in recoveryCodesList"
                                    :key="code"
                                    class="rounded bg-muted px-2 py-1 font-mono text-[11px]"
                                >
                                    {{ code }}
                                </li>
                            </ul>
                            <div class="flex flex-wrap gap-2">
                                <Button
                                    v-if="!recoveryCodesDownloaded"
                                    size="sm"
                                    variant="secondary"
                                    @click="downloadCodes"
                                    >Download (.txt)</Button
                                >
                                <Button
                                    size="sm"
                                    variant="secondary"
                                    @click="showRecoveryCodes = false"
                                    >Hide</Button
                                >
                            </div>
                            <p
                                v-if="recoveryCodesWarning"
                                class="text-xs text-amber-600"
                            >
                                Warning: Fewer than 2 recovery codes remain.
                                Regenerate soon.
                            </p>
                        </div>
                        <div
                            v-else-if="
                                recoveryCodesList.length && !showRecoveryCodes
                            "
                        >
                            <Button
                                variant="secondary"
                                size="sm"
                                @click="showRecoveryCodes = true"
                                >Show Recovery Codes</Button
                            >
                        </div>

                        <!-- Regeneration Request -->
                        <div class="space-y-3 rounded border p-4">
                            <h3
                                class="flex items-center gap-2 text-sm font-semibold"
                            >
                                <Icon
                                    icon="material-symbols-light:refresh"
                                    class="h-4 w-4"
                                />
                                Regenerate Recovery Codes
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                To regenerate, confirm with your password and a
                                current 2FA code. You'll receive an email with a
                                confirmation link. Existing codes become invalid
                                after regeneration.
                            </p>
                            <div
                                v-if="!awaitingRegenerationConfirmation"
                                class="space-y-2"
                            >
                                <input
                                    v-model="passwordForRegeneration"
                                    type="password"
                                    placeholder="Current password"
                                    class="w-full rounded border px-2 py-1 text-sm"
                                />
                                <input
                                    v-model="codeInput"
                                    maxlength="6"
                                    inputmode="numeric"
                                    placeholder="2FA code"
                                    class="w-40 rounded border px-2 py-1 text-center font-mono text-sm"
                                />
                                <Button
                                    size="sm"
                                    @click="handleRegenerationRequest"
                                    >Request Regeneration</Button
                                >
                            </div>
                            <div
                                v-else
                                class="space-y-2 text-xs text-muted-foreground"
                            >
                                <p>
                                    Confirmation email sent. After clicking the
                                    link, click below to load new codes.
                                </p>
                                <Button
                                    size="sm"
                                    @click="handleConfirmRegeneration"
                                    >I've Confirmed - Load New Codes</Button
                                >
                            </div>
                        </div>

                        <div>
                            <Button variant="destructive" @click="handleDisable"
                                ><Icon
                                    icon="material-symbols-light:shield-off"
                                    class="mr-1"
                                />
                                Disable 2FA</Button
                            >
                        </div>
                    </div>
                </div>

                <div class="mb-4 space-y-6 rounded bg-neutral-800/30 p-4">
                    <HeadingSmall
                        title="Security Key (U2F)"
                        description="Manage your security keys for two-factor authentication."
                    />

                    <WebAuthnManager />
                </div>
            </div>
        </SettingsLayout>
</template>
