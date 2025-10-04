<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Themes</h1>
    <p class="text-gray-600 mb-4">Manage installed themes.</p>

    <div v-if="loading" class="text-gray-500">Loading themes...</div>
    <div v-else-if="error" class="text-red-600">{{ error }}</div>
    <ul v-else class="list-disc ml-6">
      <li v-for="theme in themes" :key="theme.name">
        {{ theme.name }} <span class="text-gray-500">v{{ theme.version }}</span>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const loading = ref(false);
const error = ref<string | null>(null);
const themes = ref<Array<{ name: string; version: string }>>([]);

const load = () => {
  loading.value = true;
  error.value = null;

  router.get('/admin/themes', {}, {
    preserveState: true,
    onSuccess: (page) => {
      const props: any = (page as any).props || {};
      themes.value = (props.data ?? props.themes ?? []) as Array<{ name: string; version: string }>;
    },
    onError: (e: any) => {
      error.value = e?.message || 'Failed to load themes';
    },
    onFinish: () => (loading.value = false),
  });
};

onMounted(load);
</script>
