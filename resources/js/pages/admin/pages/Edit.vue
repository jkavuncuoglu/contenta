<template>
  <div class="p-6 max-w-3xl">
    <h1 class="text-2xl font-bold mb-4">Edit Page</h1>

    <div v-if="loading" class="text-gray-500">Loading...</div>
    <div v-else>
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
            :disabled="saving"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
          >
            {{ saving ? 'Saving...' : 'Save changes' }}
          </button>
          <Link href="/admin/pages" class="text-gray-600 hover:underline">Cancel</Link>
        </div>

        <p v-if="error" class="text-red-600">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue';
import { router as inertia, Link } from '@inertiajs/vue3';

// Helper to get the last path segment (page id)
function getIdFromPath(): string | null {
  try {
    const segments = window.location.pathname.split('/').filter(Boolean);
    return segments[segments.length - 1] ?? null;
  } catch {
    return null;
  }
}

const form = reactive({
  title: '',
  slug: '',
  body: ''
});

const loading = ref(false);
const saving = ref(false);
const error = ref<string | null>(null);

const load = async () => {
  loading.value = true;
  error.value = null;
  try {
    const id = getIdFromPath();
    if (!id) {
      error.value = 'Invalid page id';
      return;
    }
    inertia.get(`/admin/pages/${encodeURIComponent(id)}`, {}, {
      preserveState: true,
      onSuccess: (page) => {
        const data = (page.props && (page.props.page ?? page.props.data ?? page.props)) ?? {};
        const pageData = data.data ?? data;
        form.title = pageData?.title ?? '';
        form.slug = pageData?.slug ?? '';
        form.body = pageData?.body ?? '';
      },
      onError: (page) => {
        error.value = 'Failed to load page (Inertia)';
        console.error('Inertia load page error:', page);
      }
    });
  } catch (e: any) {
    error.value = e?.message || 'Failed to load page';
  } finally {
    loading.value = false;
  }
};

const submit = async () => {
  saving.value = true;
  error.value = null;
  try {
    const id = getIdFromPath();
    if (!id) {
      error.value = 'Invalid page id';
      return;
    }
    inertia.put(`/admin/pages/${encodeURIComponent(id)}`, { ...form }, {
      preserveState: true,
      onSuccess: () => {
        inertia.get('/admin/pages');
      },
      onError: (page) => {
        error.value = 'Failed to save page';
        console.error('Inertia save page error:', page);
      }
    });
  } catch (e: any) {
    error.value = e?.message || 'Failed to save page';
  } finally {
    saving.value = false;
  }
};

onMounted(load);
</script>
