<template>
    <AppLayout class="p-4">
        <!-- Page header -->
        <div class="flex items-center justify-between">
            <div>
                <h1
                    class="text-2xl font-semibold text-gray-900 dark:text-white"
                >
                    Create New Page
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Write and publish your new site page
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <Link
                    href="/admin/pages"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Cancel
                </Link>
            </div>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Main content -->
                <div class="space-y-6">
                    <!-- Title -->
                    <div
                        class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <label
                            for="title"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Title *
                        </label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="Enter your page title..."
                            @input="generateSlug"
                        />
                        <div
                            v-if="errors.title"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ errors.title[0] }}
                        </div>
                    </div>

                    <!-- Slug -->
                    <div
                        class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                    >
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
                            class="mt-1 block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="page-slug"
                        />
                        <div
                            v-if="errors.slug"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ errors.slug[0] }}
                        </div>
                    </div>

                    <!-- Content -->
                    <ContentEditor v-model="form.content" />
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Publish -->
                    <div
                        class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <h3
                            class="mb-4 text-lg font-medium text-gray-900 dark:text-white"
                        >
                            Publish
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label
                                    for="published"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Published
                                </label>
                                <input
                                    id="published"
                                    type="checkbox"
                                    v-model="form.published"
                                    class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                                />
                            </div>
                        </div>
                        <div class="mt-6 flex flex-col space-y-2">
                            <button
                                type="submit"
                                :disabled="loading"
                                class="flex w-full justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span v-if="loading" class="flex items-center">
                                    <div
                                        class="mr-2 h-4 w-4 animate-spin rounded-full border-b-2 border-white"
                                    ></div>
                                    Saving...
                                </span>
                                <span v-else> Save </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>

<script setup lang="ts">
import ContentEditor from '@/components/ContentEditor/ContentEditor.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

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
