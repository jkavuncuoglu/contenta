<template>
    <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader class="flex items-center justify-center">
                <div class="mb-3 w-auto rounded-full border border-border bg-card p-0.5 shadow-sm">
                    <div class="relative overflow-hidden rounded-full border border-border bg-muted p-2.5">
                        <div class="absolute inset-0 grid grid-cols-5 opacity-50">
                            <div v-for="i in 5" :key="`col-${i}`" class="border-r border-border last:border-r-0" />
                        </div>
                        <div class="absolute inset-0 grid grid-rows-5 opacity-50">
                            <div v-for="i in 5" :key="`row-${i}`" class="border-b border-border last:border-b-0" />
                        </div>
                        <Icon icon="material-symbols-light:qr_code_scanner" class="relative z-20 size-6 text-foreground" />
                    </div>
                </div>
                <DialogTitle>{{ modalConfig.title }}</DialogTitle>
                <DialogDescription class="text-center">
                    {{ modalConfig.description }}
                </DialogDescription>
            </DialogHeader>
            <div class="relative flex w-auto flex-col items-center justify-center space-y-5">
                <template v-if="!showVerificationStep && !showRecoveryCodes && !showRegenerationForm">
                    <AlertError v-if="errors?.length" :errors="errors" />
                    <template v-else>
                        <div v-if="setupData" class="flex flex-col items-center space-y-4">
                            <img :src="'data:image/png;base64,' + setupData.qrCode" alt="QR Code" class="w-48 h-48" />
                            <div class="flex items-center space-x-2">
                                <span class="font-mono text-xs">{{ setupData.manualEntry }}</span>
                                <Button @click="copy(setupData.manualEntry)">Copy</Button>
                            </div>
                            <Button @click="showVerificationStep = true">Continue</Button>
                        </div>
                    </template>
                </template>
                <template v-if="showVerificationStep">
                    <div class="flex flex-col items-center space-y-4">
                        <label for="code" class="text-sm">Enter the 6-digit code from your authenticator app</label>
                        <input v-model="codeValue" maxlength="6" class="border rounded px-2 py-1 text-center font-mono" />
                        <Button @click="handleVerificationSubmit">Verify & Enable</Button>
                    </div>
                </template>
                <template v-if="showRecoveryCodes">
                    <div class="flex flex-col items-center space-y-4">
                        <h3 class="font-semibold">Recovery Codes</h3>
                        <ul class="grid grid-cols-2 gap-2">
                            <li v-for="code in recoveryCodesList" :key="code" class="bg-muted rounded px-2 py-1 font-mono text-xs">{{ code }}</li>
                        </ul>
                        <Button v-if="!recoveryCodesDownloaded" @click="handleDownloadCodes">Download Codes</Button>
                        <p v-if="recoveryCodesWarning" class="text-xs text-warning">Warning: Less than 2 recovery codes remaining!</p>
                        <Button @click="showRegenerationForm = true">Regenerate Codes</Button>
                    </div>
                </template>
                <template v-if="showRegenerationForm">
                    <div class="flex flex-col items-center space-y-4">
                        <label for="password" class="text-sm">Password</label>
                        <input v-model="password" type="password" class="border rounded px-2 py-1" />
                        <label for="code" class="text-sm">2FA Code</label>
                        <input v-model="codeValue" maxlength="6" class="border rounded px-2 py-1 text-center font-mono" />
                        <Button @click="handleRegenerationSubmit">Request Regeneration</Button>
                    </div>
                </template>
                <template v-if="regenerationEmailSent">
                    <div class="flex flex-col items-center space-y-4">
                        <p class="text-sm">A confirmation email has been sent. Please check your inbox and click the link to confirm regeneration.</p>
                        <Button @click="handleConfirmRegeneration">I've confirmed, show codes</Button>
                    </div>
                </template>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import AlertError from '@/components/AlertError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { useClipboard } from '@vueuse/core';
import { Icon } from '@iconify/vue';
import { computed, ref, watch } from 'vue';

interface Props {
    requiresConfirmation: boolean;
    twoFactorEnabled: boolean;
}

const props = defineProps<Props>();
const isOpen = defineModel<boolean>('isOpen');

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
    clearSetupData,
} = useTwoFactorAuth();

const password = ref('');
const { copy } = useClipboard();
const showVerificationStep = ref(false);
const showRecoveryCodes = ref(false);
const showRegenerationForm = ref(false);
const regenerationEmailSent = ref(false);
const code = ref<number[]>([]);
const codeValue = computed<string>(() => code.value.join(''));
const emit = defineEmits<{ (e: 'enabled'): void }>();

const modalConfig = computed<{
    title: string;
    description: string;
    buttonText: string;
}>(() => {
    if (props.twoFactorEnabled) {
        return {
            title: 'Two-Factor Authentication Enabled',
            description:
                'Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.',
            buttonText: 'Close',
        };
    }
    if (showVerificationStep.value) {
        return {
            title: 'Verify Authentication Code',
            description: 'Enter the 6-digit code from your authenticator app',
            buttonText: 'Continue',
        };
    }
    return {
        title: 'Enable Two-Factor Authentication',
        description:
            'To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app',
        buttonText: 'Continue',
    };
});

const handleVerificationSubmit = async () => {
    const result = await validateTwoFactorCode(codeValue.value);
    if (result && result.success) {
        emit('enabled');
        showVerificationStep.value = false;
        showRecoveryCodes.value = true;
        // codes already included in enable response; optional fetch for backend-marked view later
    }
};

const handleDownloadCodes = async () => {
    await downloadCodes();
};

const handleRegenerationSubmit = async () => {
    const result = await regenerateCodes(password.value, codeValue.value);
    if (result && result.success) {
        regenerationEmailSent.value = true;
        showRegenerationForm.value = false;
    }
};

const handleConfirmRegeneration = async () => {
    await confirmCodeRegeneration();
    showRecoveryCodes.value = true;
    regenerationEmailSent.value = false;
};

const resetModalState = () => {
    if (props.twoFactorEnabled) {
        clearSetupData();
    }
    showVerificationStep.value = false;
    showRecoveryCodes.value = false;
    showRegenerationForm.value = false;
    regenerationEmailSent.value = false;
    code.value = [];
    password.value = '';
};

watch(
    () => isOpen.value,
    async (isOpen) => {
        if (!isOpen) {
            resetModalState();
            return;
        }
        await fetchSetupData();
    },
);
</script>
