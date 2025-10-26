<template>
    <Head title="Create Post" />
    <AppLayout >
    <div class="max-w-7xl mx-auto space-y-6 p-4">
        <!-- Page header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create New Post</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Write and publish your new blog post or article
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <Link
                    href="/admin/posts"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Cancel
                </Link>
            </div>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Title *
                            </label>
                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                required
                                class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                placeholder="Enter your post title..."
                                @input="generateSlug"
                            />
                            <div v-if="errors.title" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ errors.title[0] }}
                            </div>
                        </div>

                        <!-- Slug -->
                        <div class="mt-4">
                            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Slug
                            </label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                <span
                    class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-400 text-sm">
                  posts/
                </span>
                                <input
                                    id="slug"
                                    v-model="form.slug"
                                    type="text"
                                    class="p-2 border flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                    placeholder="post-slug"
                                />
                            </div>
                            <div v-if="errors.slug" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ errors.slug[0] }}
                            </div>
                        </div>
                    </div>

                    <ContentEditor v-model="form.content_markdown"/>

                    <!-- Excerpt -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Excerpt
                        </label>
                        <textarea
                            id="excerpt"
                            v-model="form.excerpt"
                            rows="3"
                            class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                            placeholder="Optional excerpt for post previews..."
                        ></textarea>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Brief description of your post. If left empty, it will be generated automatically.
                        </p>
                        <div v-if="errors.excerpt" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ errors.excerpt[0] }}
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Publish -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Publish</h3>

                        <div class="space-y-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Status
                                </label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="private">Private</option>
                                </select>
                            </div>

                            <!-- Scheduled publish date -->
                            <div v-if="form.status === 'scheduled'">
                                <label for="published_at"
                                       class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Publish Date
                                </label>
                                <input
                                    id="published_at"
                                    v-model="form.published_at"
                                    type="datetime-local"
                                    class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                />
                            </div>

                            <!-- Post Type -->
                            <div>
                                <label for="post_type"
                                       class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Post Type
                                </label>
                                <select
                                    id="post_type"
                                    v-model="form.post_type_id"
                                    required
                                    class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Select post type</option>
                                    <option value="1">Post</option>
                                    <option value="2">Page</option>
                                </select>
                                <div v-if="errors.post_type_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ errors.post_type_id[0] }}
                                </div>
                            </div>
                        </div>

                        <!-- Submit buttons -->
                        <div class="mt-6 flex flex-col space-y-2">
                            <button
                                type="submit"
                                :disabled="loading"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                <span v-if="loading" class="flex items-center">
                  <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                  {{ form.status === 'published' ? 'Publishing...' : 'Saving...' }}
                </span>
                                <span v-else>
                  {{ form.status === 'published' ? 'Publish' : 'Save Draft' }}
                </span>
                            </button>

                            <button
                                v-if="form.status === 'draft'"
                                @click="publishNow"
                                type="button"
                                :disabled="loading"
                                class="w-full flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Save & Publish
                            </button>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Categories</h3>
                        <div class="space-y-2">
                            <!-- TODO: Load categories dynamically -->
                            <div class="flex items-center">
                                <input
                                    id="category-1"
                                    type="checkbox"
                                    value="1"
                                    v-model="form.categories"
                                    class="p-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700"
                                />
                                <label for="category-1" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                    Technology
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input
                                    id="category-2"
                                    type="checkbox"
                                    value="2"
                                    v-model="form.categories"
                                    class="p-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700"
                                />
                                <label for="category-2" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                    Programming
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Tags -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Tags</h3>
                        <div>
                            <input
                                type="text"
                                v-model="tagInput"
                                @keydown.enter.prevent="addTag"
                                @keydown="handleTagKeydown"
                                placeholder="Add tags separated by commas or press Enter"
                                class="p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Press Enter or comma to add tags
                            </p>

                            <!-- Tag list -->
                            <div v-if="form.tags.length > 0" class="mt-3 flex flex-wrap gap-2">
                <span
                    v-for="(tag, index) in form.tags"
                    :key="index"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200"
                >
                  {{ tag }}
                  <button
                      @click="removeTag(index)"
                      type="button"
                      class="ml-1 inline-flex items-center p-0.5 rounded-full text-blue-400 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-800 hover:text-blue-500 dark:hover:text-blue-200"
                  >
                    <Icon name="x"/>
                  </button>
                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    </AppLayout>
</template>

<script setup lang="ts">
import {ref, reactive} from 'vue';
import { Head, router } from '@inertiajs/vue3';
import {usePostsStore} from '@/stores/posts';
import type {PostForm} from '@/types';
import ContentEditor from "@/components/ContentEditor/ContentEditor.vue";
import { Link } from '@inertiajs/vue3';
import Icon from '@/components/Icon.vue';
import AppLayout from '@/layouts/AppLayout.vue';


const postsStore = usePostsStore();

const errors = ref<Record<string, string[]>>({});

const loading = ref(false);
const tagInput = ref('');

const form = reactive<PostForm>({
    title: '',
    slug: '',
    content_markdown: '',
    excerpt: '',
    status: 'draft',
    post_type_id: 1,
    categories: [],
    tags: [],
    custom_fields: {}
});

const generateSlug = () => {
    if (!form.slug && form.title) {
        form.slug = form.title
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
};

const addTag = () => {
    const tag = tagInput.value.trim().replace(',', '');
    if (tag && !form.tags.includes(tag)) {
        form.tags.push(tag);
    }
    tagInput.value = '';
};

const handleTagKeydown = (e: KeyboardEvent) => {
  if (e.key === ',') {
    e.preventDefault();
    addTag();
  }
};

const removeTag = (index: number) => {
    form.tags.splice(index, 1);
};

const handleSubmit = async () => {
    loading.value = true;
    errors.value = {};

    const result = await postsStore.createPost(form);

    if (result.success) {
        // navigate with Inertia router
        router.visit('/admin/posts');
    } else {
        if (result.error) {
            // Handle validation errors or general errors
            errors.value = {general: [result.error]};
        }
    }

    loading.value = false;
};

const publishNow = async () => {
    form.status = 'published';
    await handleSubmit();
};
</script>

<style scoped>
.prose {
    max-width: none;
}

.prose code {
    background-color: rgb(243 244 246);
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
}

.dark .prose code {
    background-color: rgb(55 65 81);
}</style>
