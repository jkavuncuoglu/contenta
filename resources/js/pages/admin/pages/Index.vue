<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Pages</h1>
      <RouterLink
        :to="{ name: 'admin.pages.create' }"
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
      >
        New Page
      </RouterLink>
    </div>

    <div v-if="loading" class="text-gray-500">Loading pages...</div>
    <div v-else-if="error" class="text-red-600">{{ error }}</div>

    <table v-else class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-800">
        <tr>
          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
          <th class="px-4 py-2"></th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
        <tr v-for="page in pages" :key="page.id">
          <td class="px-4 py-2">{{ page.title }}</td>
          <td class="px-4 py-2 text-gray-500">{{ page.slug }}</td>
          <td class="px-4 py-2 text-right">
            <RouterLink
              :to="{ name: 'admin.pages.edit', params: { id: page.id } }"
              class="text-blue-600 hover:underline"
            >
              Edit
            </RouterLink>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { router as inertia } from '@inertiajs/vue3';

interface CmsPage { id: number; title: string; slug: string; }

const pages = ref<CmsPage[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);

const loadPages = async () => {
  loading.value = true;
  error.value = null;
  try {
    inertia.get('/admin/pages', {}, {
      preserveState: true,
      onSuccess: (page) => {
        // Try a few common prop locations for data
        const data = (page.props && (page.props.pages ?? page.props.data)) ?? page.props ?? {};
        pages.value = data.data ?? data ?? [];
      },
      onError: (page) => {
        error.value = 'Failed to load pages (Inertia)';
        console.error('Inertia error loading pages:', page);
      }
    });
  } catch (e: any) {
    error.value = e?.message || 'Failed to load pages';
  } finally {
    loading.value = false;
  }
};

onMounted(loadPages);
</script>
