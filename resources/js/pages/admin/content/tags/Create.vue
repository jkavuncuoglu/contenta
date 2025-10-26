<template>
    <Head title="Create Tags" />
    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                    >
                        Create Tag
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Add a new tag to organize your content
                    </p>
                </div>
                <Link
                    href="/admin/tags"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-gray-700"
                >
                    <Icon icon="lucide:arrow-left" class="mr-2 h-4 w-4" />
                    Back to Tags
                </Link>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="rounded-lg bg-white shadow dark:bg-gray-800">
                    <div
                        class="border-b border-gray-200 px-6 py-4 dark:border-gray-700"
                    >
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-white"
                        >
                            Tag Details
                        </h3>
                    </div>

                    <div class="space-y-6 p-6">
                        <!-- Name -->
                        <div>
                            <label
                                for="name"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700"
                                placeholder="Enter tag name"
                                @input="generateSlug"
                            />
                            <p
                                v-if="errors.name"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.name[0] }}
                            </p>
                        </div>

                        <!-- Slug -->
                        <div>
                            <label
                                for="slug"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Slug
                            </label>
                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700"
                                placeholder="tag-slug"
                            />
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                URL-friendly version of the name. Leave empty to
                                auto-generate.
                            </p>
                            <p
                                v-if="errors.slug"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.slug[0] }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label
                                for="description"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Description
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700"
                                placeholder="Brief description of this tag"
                            ></textarea>
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                {{ (form.description || '').length }}/1000
                                characters
                            </p>
                            <p
                                v-if="errors.description"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.description[0] }}
                            </p>
                        </div>

                        <!-- Color -->
                        <div>
                            <label
                                for="color"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
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
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700"
                                />
                            </div>
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                Choose a color to help identify this tag
                            </p>
                            <p
                                v-if="errors.color"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.color[0] }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- SEO Section -->
                <div class="rounded-lg bg-white shadow dark:bg-gray-800">
                    <div
                        class="border-b border-gray-200 px-6 py-4 dark:border-gray-700"
                    >
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-white"
                        >
                            SEO Settings
                        </h3>
                    </div>

                    <div class="space-y-6 p-6">
                        <!-- Meta Title -->
                        <div>
                            <label
                                for="meta_title"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Meta Title
                            </label>
                            <input
                                id="meta_title"
                                v-model="form.meta_title"
                                type="text"
                                maxlength="255"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700"
                                placeholder="SEO title for search engines"
                            />
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                {{ (form.meta_title || '').length }}/255
                                characters
                            </p>
                            <p
                                v-if="errors.meta_title"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.meta_title[0] }}
                            </p>
                        </div>

                        <!-- Meta Description -->
                        <div>
                            <label
                                for="meta_description"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Meta Description
                            </label>
                            <textarea
                                id="meta_description"
                                v-model="form.meta_description"
                                rows="3"
                                maxlength="500"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700"
                                placeholder="Brief description for search engines"
                            ></textarea>
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                {{ (form.meta_description || '').length }}/500
                                characters
                            </p>
                            <p
                                v-if="errors.meta_description"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.meta_description[0] }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4">
                    <Link
                        href="/admin/tags"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-blue-900 disabled:opacity-50"
                    >
                        <div
                            v-if="isLoading"
                            class="mr-2 h-4 w-4 animate-spin rounded-full border-b-2 border-white"
                        ></div>
                        {{ isLoading ? 'Creating...' : 'Create Tag' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useTagsStore } from '@/stores/tags';
import type { CreateTagData, TagForm } from '@/types';
import { Icon } from '@iconify/vue';
import { Head, router as inertia, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const inertiaRouter = inertia;
const tagsStore = useTagsStore();

// Form state
const form = ref<TagForm>({
    name: '',
    slug: '',
    description: '',
    color: '#3b82f6',
    meta_title: '',
    meta_description: '',
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
            meta_description: form.value.meta_description || undefined,
        };

        await tagsStore.createTag(data);
        inertiaRouter.get('/admin/tags');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }
    }
};
</script>
