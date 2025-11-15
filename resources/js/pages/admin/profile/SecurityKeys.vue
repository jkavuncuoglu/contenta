<template>
  <div class="max-w-2xl">
    <h2 class="text-xl font-bold mb-4">Security Keys</h2>

    <div class="space-y-3">
      <div class="flex gap-2">
        <input v-model="nickname" class="border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" placeholder="Key nickname (optional)" />
        <button :disabled="registering" @click="register" class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
          {{ registering ? 'Waiting for key...' : 'Register new security key' }}
        </button>
      </div>

      <div v-if="loading" class="text-gray-500">Loading credentials...</div>
      <div v-else>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700 rounded border border-gray-200 dark:border-gray-700">
          <li v-for="cred in credentials" :key="cred.id" class="flex items-center justify-between p-3">
            <div>
              <p class="font-medium">{{ cred.name || cred.id }}</p>
              <p class="text-sm text-gray-500">Added: {{ cred.created_at }}</p>
            </div>
            <button @click="remove(cred.id)" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">Remove</button>
          </li>
        </ul>
        <p v-if="!credentials.length" class="text-gray-500">No security keys registered.</p>
      </div>

      <p v-if="error" class="text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { base64urlToArrayBuffer, arrayBufferToBase64url } from '@/lib/webauthn';

type PublicKeyCredentialCreationOptions = globalThis.PublicKeyCredentialCreationOptions;

interface Credential {
  id: string;
  name?: string;
  created_at?: string;
}

const nickname = ref('');
const credentials = ref<Credential[]>([]);
const loading = ref(false);
const registering = ref(false);
const error = ref<string | null>(null);

const load = async () => {
  loading.value = true;
  error.value = null;
  try {
    const res = await fetch('/auth/webauthn/credentials', { credentials: 'same-origin' });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    credentials.value = data.data ?? data ?? [];
  } catch (e: any) {
    error.value = e?.message || 'Failed to load credentials';
  } finally {
    loading.value = false;
  }
};

const register = async () => {
  registering.value = true;
  error.value = null;
  try {
    const res = await fetch('/auth/webauthn/options', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name: nickname.value || undefined }),
      credentials: 'same-origin',
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    const pubKey: PublicKeyCredentialCreationOptions = {
      ...data,
      challenge: base64urlToArrayBuffer(data.challenge),
      user: { ...data.user, id: base64urlToArrayBuffer(data.user.id) },
      excludeCredentials: (data.excludeCredentials || []).map((c: any) => ({
        ...c,
        id: base64urlToArrayBuffer(c.id),
      })),
    };
    // @ts-expect-error WebAuthn API typings not available in project lib
    const cred: PublicKeyCredential = await navigator.credentials.create({ publicKey: pubKey });
    const attestation = {
      id: cred.id,
      rawId: arrayBufferToBase64url(cred.rawId),
      type: cred.type,
      response: {
        // @ts-expect-error Response properties not typed reliably by TS for this API
        attestationObject: arrayBufferToBase64url(cred.response.attestationObject),
        // @ts-expect-error Response properties not typed reliably by TS for this API
        clientDataJSON: arrayBufferToBase64url(cred.response.clientDataJSON),
      },
    };

    const regRes = await fetch('/auth/webauthn/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(attestation),
      credentials: 'same-origin',
    });
    if (!regRes.ok) throw new Error(`HTTP ${regRes.status}`);

    nickname.value = '';
    await load();
  } catch (e: any) {
    error.value = e?.message || 'Security key registration failed';
  } finally {
    registering.value = false;
  }
};

const remove = async (id: string) => {
  try {
    const res = await fetch(`/auth/webauthn/credentials/${encodeURIComponent(id)}`, {
      method: 'DELETE',
      credentials: 'same-origin',
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    await load();
  } catch (e: any) {
    error.value = e?.message || 'Failed to remove credential';
  }
};

onMounted(load);
</script>
