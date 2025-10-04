<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Plugins</h1>
    <p class="text-gray-600 mb-4">Manage installed plugins.</p>

    <div v-if="loading" class="text-gray-500">Loading plugins...</div>
    <div v-else-if="error" class="text-red-600">{{ error }}</div>
    <ul v-else class="list-disc ml-6">
      <li v-for="plugin in plugins" :key="plugin.name">
        {{ plugin.name }} <span class="text-gray-500">v{{ plugin.version }}</span>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const loading = ref(false);
const error = ref<string | null>(null);
const plugins = ref<Array<{ name: string; version: string }>>([]);

const load = () => {
  loading.value = true;
  error.value = null;

  router.get('/admin/plugins', {}, {
    preserveState: true,
    onSuccess: (page) => {
      const props: any = (page as any).props || {};
      plugins.value = (props.data ?? props.plugins ?? []) as Array<{ name: string; version: string }>;
    },
    onError: (e: any) => {
      error.value = e?.message || 'Failed to load plugins';
    },
    onFinish: () => {
      loading.value = false;
    },
  });
};

onMounted(load);
</script>
