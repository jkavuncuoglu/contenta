<template>
    <Head title="Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white"
                    >
                        Categories
                    </h1>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        Manage your content categories and their hierarchy
                    </p>
                </div>
                <Link
                    href="/admin/categories/create"
                    class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-blue-900"
                >
                    <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
                    Add Category
                </Link>
            </div>

            <!-- Categories List -->
            <div class="rounded-lg bg-white shadow dark:bg-neutral-800">
                <div
                    class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700"
                >
                    <h3
                        class="text-lg font-medium text-neutral-900 dark:text-white"
                    >
                        Categories ({{ categoriesArray.length }})
                    </h3>
                </div>

                <div v-if="!categoriesArray.length" class="p-12 text-center">
                    <Icon
                        icon="lucide:folder"
                        class="mx-auto h-12 w-12 text-neutral-400"
                    />
                    <h3
                        class="mt-2 text-sm font-medium text-neutral-900 dark:text-white"
                    >
                        No categories
                    </h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Get started by creating a new category.
                    </p>
                    <div class="mt-6">
                        <Link
                            href="/admin/categories/create"
                            class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-700"
                        >
                            <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
                            Add Category
                        </Link>
                    </div>
                </div>

                <div v-else>
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700"
                        >
                            <thead class="bg-neutral-50 dark:bg-neutral-900">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-neutral-500 uppercase dark:text-neutral-400"
                                    >
                                        Name
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-neutral-500 uppercase dark:text-neutral-400"
                                    >
                                        Slug
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-neutral-500 uppercase dark:text-neutral-400"
                                    >
                                        Parent
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-neutral-500 uppercase dark:text-neutral-400"
                                    >
                                        Posts
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-neutral-500 uppercase dark:text-neutral-400"
                                    >
                                        Created
                                    </th>
                                    <th class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800"
                            >
                                <tr
                                    v-for="category in categoriesArray"
                                    :key="category.id"
                                    class="hover:bg-neutral-50 dark:hover:bg-neutral-700"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div
                                                    class="text-sm font-medium text-neutral-900 dark:text-white"
                                                >
                                                    {{ category.name }}
                                                </div>
                                                <div
                                                    v-if="category.description"
                                                    class="max-w-xs truncate text-sm text-neutral-500 dark:text-neutral-400"
                                                >
                                                    {{ category.description }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm whitespace-nowrap text-neutral-500 dark:text-neutral-400"
                                    >
                                        {{ category.slug }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm whitespace-nowrap text-neutral-500 dark:text-neutral-400"
                                    >
                                        {{ category.parent?.name || '—' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm whitespace-nowrap text-neutral-500 dark:text-neutral-400"
                                    >
                                        {{ category.posts_count || 0 }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm whitespace-nowrap text-neutral-500 dark:text-neutral-400"
                                    >
                                        {{ formatDate(category.created_at) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                                    >
                                        <div
                                            class="flex items-center justify-end space-x-2"
                                        >
                                            <Link
                                                :href="`/admin/categories/${category.id}/edit`"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            >
                                                <Icon
                                                    icon="lucide:pencil"
                                                    class="h-4 w-4"
                                                />
                                            </Link>
                                            <button
                                                @click="
                                                    deleteCategory(category)
                                                "
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            >
                                                <Icon
                                                    icon="lucide:trash"
                                                    class="h-4 w-4"
                                                />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Icon } from '@iconify/vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, defineProps } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    posts_count?: number;
    created_at: string;
    parent?: {
        name: string;
    };
}

interface Props {
    categories: Category[];
}

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
        required: false,
    },
    meta: {
        type: Object,
        default: () => ({
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
        }),
        required: false,
    },
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Categories',
        href: '/admin/categories',
    },
];

const deleteCategory = (category: Category) => {
    if (
        confirm(
            `Are you sure you want to delete the category "${category.name}"?`,
        )
    ) {
        router.delete(`/admin/categories/${category.id}`);
    }
};

const categoriesArray = computed(() => {
    return props.categories;
});

const formatDate = (dateString: string) => {
    const options: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    };
    return new Date(dateString).toLocaleString('en-US', options);
};
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
