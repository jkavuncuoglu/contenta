<template>
  <div class="max-w-2xl">
    <h2 class="text-xl font-bold mb-4">Social Accounts</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="border rounded p-4">
        <p class="font-medium mb-2">Google</p>
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-500">
            {{ linked.google ? 'Connected' : 'Not connected' }}
          </span>
          <button
            v-if="!linked.google"
            @click="connect('google')"
            class="px-3 py-1 bg-blue-600 text-white rounded"
          >
            Connect
          </button>
          <button
            v-else
            @click="disconnect('google')"
            class="px-3 py-1 bg-red-600 text-white rounded"
          >
            Disconnect
          </button>
        </div>
      </div>

      <div class="border rounded p-4">
        <p class="font-medium mb-2">GitHub</p>
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-500">
            {{ linked.github ? 'Connected' : 'Not connected' }}
          </span>
          <button
            v-if="!linked.github"
            @click="connect('github')"
            class="px-3 py-1 bg-blue-600 text-white rounded"
          >
            Connect
          </button>
          <button
            v-else
            @click="disconnect('github')"
            class="px-3 py-1 bg-red-600 text-white rounded"
          >
            Disconnect
          </button>
        </div>
      </div>
    </div>

    <p v-if="error" class="text-red-600 dark:text-red-400 mt-3">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const error = computed(() => auth.error);

const linked = reactive<{ google: boolean; github: boolean }>({
  google: false,
  github: false
});

const load = async () => {
  auth.clearError();
  try {
    const res = await fetch('/auth/social/providers', { credentials: 'same-origin' });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    const providers = data.data ?? data ?? {};
    linked.google = !!providers.google;
    linked.github = !!providers.github;
  } catch (e: any) {
    // handled by store or show minimal error
    auth.setError?.(e?.message || 'Failed to load social providers');
  }
};

const connect = async (provider: 'google' | 'github') => {
  try {
    const res = await fetch(`/auth/social/${provider}/redirect?link=1`, { credentials: 'same-origin' });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    const url = data.url || data.data?.url;
    if (url) {
      window.location.href = url;
    }
  } catch (e: any) {
    // handled by store
    auth.setError?.(e?.message || 'Failed to connect provider');
  }
};

const disconnect = async (provider: 'google' | 'github') => {
  try {
    const res = await fetch(`/auth/social/${provider}/unlink`, {
      method: 'DELETE',
      credentials: 'same-origin',
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    await load();
  } catch (e: any) {
    auth.setError?.(e?.message || 'Failed to disconnect provider');
  }
};

onMounted(load);
</script>
