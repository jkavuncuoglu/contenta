<template>
  <div class="max-w-2xl">
    <h2 class="text-xl font-bold mb-4">Two-Factor Authentication (TOTP)</h2>

    <div v-if="loading" class="text-gray-500">Loading...</div>
    <div v-else>
      <div v-if="enabled" class="space-y-4">
        <p class="text-green-700 dark:text-green-400">Two-factor authentication is enabled.</p>

        <div v-if="qrSvg" class="border rounded p-4 bg-white dark:bg-gray-800">
          <div class="prose dark:prose-invert" v-html="qrSvg"></div>
          <p class="mt-2 text-sm text-gray-500">Scan this QR code with your authenticator app if you need to re-setup.</p>
        </div>

        <div class="space-y-2">
          <h3 class="font-semibold">Recovery Codes</h3>
          <ul class="list-disc ml-6">
            <li v-for="code in recoveryCodes" :key="code" class="font-mono">{{ code }}</li>
          </ul>
          <div class="flex gap-3 mt-2">
            <button @click="regenerate" class="px-3 py-2 bg-gray-700 text-white rounded hover:bg-gray-800">Regenerate Codes</button>
            <button @click="loadQr" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 dark:text-white rounded">Show QR</button>
          </div>
        </div>

        <button
          :disabled="saving"
          @click="disable"
          class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50"
        >
          {{ saving ? 'Disabling...' : 'Disable 2FA' }}
        </button>
      </div>

      <div v-else class="space-y-4">
        <p class="text-gray-700 dark:text-gray-300">Protect your account by enabling two-factor authentication.</p>
        <div v-if="qrSvg" class="border rounded p-4 bg-white dark:bg-gray-800">
          <div class="prose dark:prose-invert" v-html="qrSvg"></div>
          <p class="mt-2 text-sm text-gray-500">Scan the QR code and enter the 6-digit code to confirm.</p>
          <div class="flex gap-2 mt-2">
            <input v-model="code" class="border rounded px-3 py-2 w-40 dark:bg-gray-800 dark:border-gray-700" placeholder="123456" />
            <button :disabled="saving" @click="confirmEnable" class="px-3 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
              {{ saving ? 'Confirming...' : 'Confirm' }}
            </button>
          </div>
        </div>
        <button :disabled="loadingQr" @click="enable" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
          {{ loadingQr ? 'Preparing...' : 'Enable 2FA' }}
        </button>
      </div>

      <p v-if="error" class="text-red-600 dark:text-red-400 mt-3">{{ error }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();

const loading = ref(false);
const saving = ref(false);
const loadingQr = ref(false);
const qrSvg = ref<string | null>(null);
const code = ref('');

const error = computed(() => auth.error);
const enabled = computed(() => !!(auth.user as any)?.two_factor_enabled || !!(auth.user as any)?.two_factor_secret);
const recoveryCodes = ref<string[]>([]);

const loadStatus = async () => {
  // Optionally load recovery codes or status from API
  const r = await auth.getTwoFactorRecoveryCodes();
  if (r.success && Array.isArray(r.data)) {
    recoveryCodes.value = r.data;
  }
};

const loadQr = async () => {
  const r = await auth.getTwoFactorQrCode();
  if (r.success) {
    qrSvg.value = r.svg || null;
  }
};

const enable = async () => {
  loadingQr.value = true;
  auth.clearError();
  const r = await auth.enableTwoFactor();
  loadingQr.value = false;
  if (r.success) {
    await loadQr();
    await loadStatus();
  }
};

const confirmEnable = async () => {
  saving.value = true;
  auth.clearError();
  try {
    const r = await auth.confirmTwoFactor(code.value);
    if (r.success) {
      await auth.fetchUser();
    }
  } finally {
    saving.value = false;
  }
};

const regenerate = async () => {
  const r = await auth.regenerateTwoFactorRecoveryCodes();
  if (r.success && Array.isArray(r.data)) {
    recoveryCodes.value = r.data;
  }
};

const disable = async () => {
  saving.value = true;
  auth.clearError();
  try {
    const r = await auth.disableTwoFactor();
    if (r.success) {
      qrSvg.value = null;
      code.value = '';
      await auth.fetchUser();
    }
  } finally {
    saving.value = false;
  }
};

onMounted(async () => {
  await loadStatus();
});
</script>
