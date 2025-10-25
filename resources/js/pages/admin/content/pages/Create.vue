<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Page header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create New Page</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Write and publish your new site page
          </p>
        </div>
        <div class="flex items-center space-x-3">
          <Link
            href="/admin/pages"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancel
          </Link>
        </div>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Main content -->
          <div class="space-y-6">
            <!-- Title -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
              <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Title *
              </label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                required
                class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                placeholder="Enter your page title..."
                @input="generateSlug"
              />
              <div v-if="errors.title" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.title[0] }}
              </div>
            </div>

            <!-- Slug -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
              <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Slug
              </label>
              <input
                id="slug"
                v-model="form.slug"
                type="text"
                class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                placeholder="page-slug"
              />
              <div v-if="errors.slug" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.slug[0] }}
              </div>
            </div>

            <!-- Content -->
            <ContentEditor v-model="form.content"/>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Publish -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Publish</h3>
              <div class="space-y-4">
                <div>
                  <label for="published" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Published
                  </label>
                  <input
                    id="published"
                    type="checkbox"
                    v-model="form.published"
                    class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700"
                  />
                </div>
              </div>
              <div class="mt-6 flex flex-col space-y-2">
                <button
                  type="submit"
                  :disabled="loading"
                  class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="loading" class="flex items-center">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    Saving...
                  </span>
                  <span v-else>
                    Save
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import ContentEditor from '@/components/ContentEditor/ContentEditor.vue';
import { Link } from '@inertiajs/vue3';

const errors = ref<Record<string, string[]>>({});
const loading = ref(false);
const form = reactive({
  title: '',
  slug: '',
  content: '',
  published: false,
});

const generateSlug = () => {
  if (!form.slug && form.title) {
    form.slug = form.title
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }
};

const handleSubmit = async () => {
  loading.value = true;
  errors.value = {};
  try {
    await router.post('/admin/pages', form);
    router.visit('/admin/pages');
  } catch (e: any) {
    errors.value = e?.response?.data?.errors || { general: [e.message] };
  }
  loading.value = false;
};
</script>
