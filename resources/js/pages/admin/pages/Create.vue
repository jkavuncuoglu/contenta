<template>
  <div class="p-6 max-w-3xl">
    <h1 class="text-2xl font-bold mb-4">Create Page</h1>

    <form class="space-y-4" @submit.prevent="submit">
      <div>
        <label class="block text-sm font-medium mb-1">Title</label>
        <input
          v-model="form.title"
          type="text"
          class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700"
          required
        />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Slug</label>
        <input
          v-model="form.slug"
          type="text"
          class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700"
          required
        />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Content</label>
        <textarea
          v-model="form.body"
          rows="10"
          class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700"
        />
      </div>

      <div class="flex items-center gap-3">
        <button
          type="submit"
          :disabled="loading"
          class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
        >
          {{ loading ? 'Saving...' : 'Save' }}
        </button>
        <RouterLink :to="{ name: 'admin.pages' }" class="text-gray-600 hover:underline">Cancel</RouterLink>
      </div>

      <p v-if="error" class="text-red-600">{{ error }}</p>
    </form>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { router as inertia } from '@inertiajs/vue3';
import { useRouter } from 'vue-router';

const router = useRouter();

const form = reactive({
  title: '',
  slug: '',
  body: ''
});

const loading = ref(false);
const error = ref<string | null>(null);

const submit = async () => {
  loading.value = true;
  error.value = null;
  try {
    inertia.post('/admin/pages', { ...form }, {
      preserveState: true,
      onSuccess: () => {
        router.push({ name: 'admin.pages' });
      },
      onError: (page) => {
        // Inertia validation errors are typically on page.props.errors
        error.value = 'Failed to create page';
        console.error('Create page error (Inertia):', page);
      }
    });
  } catch (e: any) {
    error.value = e?.message || 'Failed to create page';
  } finally {
    loading.value = false;
  }
};
</script>
