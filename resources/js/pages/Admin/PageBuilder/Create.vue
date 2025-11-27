<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { onMounted, reactive, ref, watch } from 'vue';

interface Layout {
    id: number;
    name: string;
    slug: string;
}

interface Props {
    layouts: Layout[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Pages',
        href: '/admin/page-builder',
    },
    {
        title: 'Create Page',
        href: '/admin/page-builder/create',
    },
];

const creating = ref(false);

const form = reactive({
    title: '',
    slug: '',
    layout_id: '',
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
});

// Auto-generate slug from title
watch(
    () => form.title,
    (newTitle) => {
        if (newTitle && !form.slug) {
            form.slug = newTitle
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        }
    },
);

const createPage = async () => {
    try {
        creating.value = true;

        // Use fetch for API call since we're dealing with JSON endpoints
        const response = await fetch('/admin/page-builder/api/pages', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || '',
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify(form),
        });

        if (response.ok) {
            const page = await response.json();
            router.visit(`/admin/page-builder/${page.id}/edit`);
        } else {
            const errors = await response.json();
            console.error('Failed to create page:', errors);
            alert(
                'Failed to create page. Please check the form and try again.',
            );
        }
    } catch (error) {
        console.error('Error creating page:', error);
        alert('Failed to create page. Please try again.');
    } finally {
        creating.value = false;
    }
};

const goBack = () => {
    router.visit('/admin/page-builder');
};

onMounted(() => {
    // Set default layout if only one available
    if (props.layouts.length === 1) {
        form.layout_id = props.layouts[0].id.toString();
    }
});
</script>

<template>
    <Head title="Create Page - Pages" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Page header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-semibold text-gray-900 dark:text-white"
                    >
                        Create New Page
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Build a new page with the Pages
                    </p>
                </div>
            </div>

            <!-- Form -->
            <div class="max-w-2xl">
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <form @submit.prevent="createPage" class="space-y-6">
                        <div>
                            <label
                                for="title"
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Page Title *
                            </label>
                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                required
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Enter page title"
                            />
                        </div>

                        <div>
                            <label
                                for="slug"
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Page Slug
                            </label>
                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="page-slug (auto-generated from title)"
                            />
                        </div>

                        <div>
                            <label
                                for="layout"
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Page Layout
                            </label>
                            <select
                                id="layout"
                                v-model="form.layout_id"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Select a layout</option>
                                <option
                                    v-for="layout in layouts"
                                    :key="layout.id"
                                    :value="layout.id"
                                >
                                    {{ layout.name }}
                                </option>
                            </select>
                        </div>

                        <div class="border-t pt-6 dark:border-gray-600">
                            <h3
                                class="mb-4 text-lg font-medium text-gray-900 dark:text-white"
                            >
                                SEO Settings
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label
                                        for="meta_title"
                                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Meta Title
                                    </label>
                                    <input
                                        id="meta_title"
                                        v-model="form.meta_title"
                                        type="text"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="SEO title for search engines"
                                    />
                                </div>

                                <div>
                                    <label
                                        for="meta_description"
                                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Meta Description
                                    </label>
                                    <textarea
                                        id="meta_description"
                                        v-model="form.meta_description"
                                        rows="3"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="Brief description for search engines"
                                    ></textarea>
                                </div>

                                <div>
                                    <label
                                        for="meta_keywords"
                                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Meta Keywords
                                    </label>
                                    <input
                                        id="meta_keywords"
                                        v-model="form.meta_keywords"
                                        type="text"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="keyword1, keyword2, keyword3"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex justify-end space-x-3 border-t pt-6 dark:border-gray-600"
                        >
                            <button
                                type="button"
                                @click="goBack"
                                class="rounded-md border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="!form.title || creating"
                                class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{ creating ? 'Creating...' : 'Create Page' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
