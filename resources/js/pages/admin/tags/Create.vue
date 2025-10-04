<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Create Tag</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Add a new tag to organize your content
        </p>
      </div>
      <router-link
        to="/admin/tags"
        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
      >
        <ArrowLeftIcon class="w-4 h-4 mr-2" />
        Back to Tags
      </router-link>
    </div>

    <!-- Form -->
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tag Details</h3>
        </div>

        <div class="p-6 space-y-6">
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Name <span class="text-red-500">*</span>
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="Enter tag name"
              @input="generateSlug"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
          </div>

          <!-- Slug -->
          <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Slug
            </label>
            <input
              id="slug"
              v-model="form.slug"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="tag-slug"
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              URL-friendly version of the name. Leave empty to auto-generate.
            </p>
            <p v-if="errors.slug" class="mt-1 text-sm text-red-600">{{ errors.slug[0] }}</p>
          </div>

          <!-- Description -->
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Description
            </label>
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="Brief description of this tag"
            ></textarea>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {{ (form.description || '').length }}/1000 characters
            </p>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
          </div>

          <!-- Color -->
          <div>
            <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Color
            </label>
            <div class="mt-1 flex items-center space-x-3">
              <input
                id="color"
                v-model="form.color"
                type="color"
                class="h-10 w-20 rounded border border-gray-300 dark:border-gray-600"
              />
              <input
                v-model="form.color"
                type="text"
                placeholder="#000000"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Choose a color to help identify this tag
            </p>
            <p v-if="errors.color" class="mt-1 text-sm text-red-600">{{ errors.color[0] }}</p>
          </div>
        </div>
      </div>

      <!-- SEO Section -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">SEO Settings</h3>
        </div>

        <div class="p-6 space-y-6">
          <!-- Meta Title -->
          <div>
            <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Meta Title
            </label>
            <input
              id="meta_title"
              v-model="form.meta_title"
              type="text"
              maxlength="255"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="SEO title for search engines"
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {{ (form.meta_title || '').length }}/255 characters
            </p>
            <p v-if="errors.meta_title" class="mt-1 text-sm text-red-600">{{ errors.meta_title[0] }}</p>
          </div>

          <!-- Meta Description -->
          <div>
            <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Meta Description
            </label>
            <textarea
              id="meta_description"
              v-model="form.meta_description"
              rows="3"
              maxlength="500"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="Brief description for search engines"
            ></textarea>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {{ (form.meta_description || '').length }}/500 characters
            </p>
            <p v-if="errors.meta_description" class="mt-1 text-sm text-red-600">{{ errors.meta_description[0] }}</p>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end space-x-4">
        <router-link
          to="/admin/tags"
          class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
        >
          Cancel
        </router-link>
        <button
          type="submit"
          :disabled="isLoading"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
        >
          <div v-if="isLoading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
          {{ isLoading ? 'Creating...' : 'Create Tag' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useTagsStore } from '@/stores/tags';
import type { TagForm, CreateTagData } from '@/types';
import { ArrowLeftIcon } from 'lucide-vue-next';

const router = useRouter();
const tagsStore = useTagsStore();

// Form state
const form = ref<TagForm>({
  name: '',
  slug: '',
  description: '',
  color: '#3b82f6',
  meta_title: '',
  meta_description: ''
});

const errors = ref<Record<string, string[]>>({});

// Computed properties
const { isLoading } = tagsStore;

// Methods
const generateSlug = () => {
  if (!form.value.slug && form.value.name) {
    form.value.slug = form.value.name
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)/g, '');
  }
};

const handleSubmit = async () => {
  errors.value = {};

  try {
    const data: CreateTagData = {
      name: form.value.name,
      slug: form.value.slug || undefined,
      description: form.value.description || undefined,
      color: form.value.color || undefined,
      meta_title: form.value.meta_title || undefined,
      meta_description: form.value.meta_description || undefined
    };

    await tagsStore.createTag(data);
    router.push('/admin/tags');
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
  }
};
</script>