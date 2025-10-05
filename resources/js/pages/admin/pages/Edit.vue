<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="text-center py-12">
      <p class="text-red-600 dark:text-red-400">{{ error }}</p>
      <button @click="fetchPage" class="mt-2 text-blue-600 dark:text-blue-400 hover:text-blue-500">
        Try again
      </button>
    </div>

    <!-- Edit form -->
    <div v-else-if="currentPage">
      <!-- Page header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Page</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Update your site page
          </p>
        </div>
        <div class="flex items-center space-x-3">
          <Link
            href="/admin/pages"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Back to Pages
          </Link>
        </div>
      </div>

      <!-- Edit form -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <form @submit.prevent="handleSubmit">
          <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title *</label>
          <input
            id="title"
            v-model="form.title"
            type="text"
            required
            class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
            placeholder="Enter your page title..."
          />
          <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4">Slug</label>
          <input
            id="slug"
            v-model="form.slug"
            type="text"
            class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
            placeholder="page-slug"
          />
          <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4">Content</label>
          <ContentEditor v-model="form.content"/>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4">Published</label>
          <input
            type="checkbox"
            v-model="form.published"
            class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700"
          />
          <div class="mt-6 flex space-x-2">
            <button
              type="submit"
              :disabled="loading"
              class="flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading" class="flex items-center">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                Saving...
              </span>
              <span v-else>
                Save
              </span>
            </button>
            <button
              type="button"
              @click="deletePage"
              class="flex justify-center py-2 px-4 border border-red-600 rounded-md shadow-sm text-sm font-medium text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
              Delete
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Page not found -->
    <div v-else class="text-center py-12">
      <p class="text-gray-500 dark:text-gray-400">Page not found</p>
      <Link
        href="/admin/pages"
        class="mt-2 inline-block text-blue-600 dark:text-blue-400 hover:text-blue-500"
      >
        Back to Pages
      </Link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import ContentEditor from '@/components/ContentEditor/ContentEditor.vue';

interface Props {
  id: number | string;
}
const props = defineProps<Props>();

const loading = ref(false);
const error = ref('');
const currentPage = ref<any>(null);
const form = reactive({
  title: '',
  slug: '',
  content: '',
  published: false,
});

const fetchPage = async () => {
  loading.value = true;
  error.value = '';
  try {
    // Replace with actual API call or Inertia page prop
    // For now, simulate fetch
    // You should replace this with your actual fetch logic
    // Example: await router.get(`/admin/pages/${props.id}`)
    // For now, just set dummy data
    currentPage.value = {
      id: props.id,
      title: 'Sample Page',
      slug: 'sample-page',
      content: 'Sample content',
      published: true,
    };
    Object.assign(form, currentPage.value);
  } catch (e: any) {
    error.value = e?.message || 'Failed to load page.';
  }
  loading.value = false;
};

const handleSubmit = async () => {
  loading.value = true;
  error.value = '';
  try {
    await router.put(`/admin/pages/${props.id}`, form);
    router.visit('/admin/pages');
  } catch (e: any) {
    error.value = e?.response?.data?.errors?.general?.[0] || e.message;
  }
  loading.value = false;
};

const deletePage = async () => {
  if (confirm('Are you sure you want to delete this page?')) {
    loading.value = true;
    try {
      await router.delete(`/admin/pages/${props.id}`);
      router.visit('/admin/pages');
    } catch (e: any) {
      error.value = e?.response?.data?.errors?.general?.[0] || e.message;
    }
    loading.value = false;
  }
};

onMounted(() => {
  fetchPage();
});
</script>
