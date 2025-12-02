<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Page {
    id: number;
    title: string;
    slug: string;
    status: 'draft' | 'published' | 'archived';
    updated_at: string;
    author?: {
        id: number;
        name: string;
    };
}

interface PaginatedPages {
    data: Page[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    pages: PaginatedPages;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: 'Pages', href: '/admin/pages' },
]);

const searchQuery = ref('');
const statusFilter = ref('all');

const filteredPages = computed(() => {
    return props.pages.data;
});

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'published':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'draft':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'archived':
            return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900 dark:text-neutral-300';
        default:
            return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900 dark:text-neutral-300';
    }
};

const deletePage = (id: number) => {
    if (confirm('Are you sure you want to delete this page?')) {
        router.delete(`/admin/pages/${id}`, {
            preserveScroll: true,
        });
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Pages - Page Builder" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-neutral-900 dark:text-white">
                        Pages
                    </h1>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Manage your website pages using markdown and shortcodes
                    </p>
                </div>
                <Link
                    href="/admin/pages/create"
                    class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <svg
                        class="mr-2 h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        ></path>
                    </svg>
                    Create New Page
                </Link>
            </div>

            <!-- Filters -->
            <div class="rounded-lg bg-white p-4 shadow dark:bg-neutral-800">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label
                            for="search"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            Search
                        </label>
                        <input
                            id="search"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search pages..."
                            class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                        />
                    </div>

                    <div>
                        <label
                            for="status"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            Status
                        </label>
                        <select
                            id="status"
                            v-model="statusFilter"
                            class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                        >
                            <option value="all">All Statuses</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Pages List -->
            <div class="rounded-lg bg-white shadow dark:bg-neutral-800">
                <div v-if="filteredPages.length === 0" class="p-12 text-center">
                    <svg
                        class="mx-auto h-12 w-12 text-neutral-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        ></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">
                        No pages found
                    </h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Get started by creating a new page.
                    </p>
                    <div class="mt-6">
                        <Link
                            href="/admin/pages/create"
                            class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <svg
                                class="mr-2 h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                ></path>
                            </svg>
                            Create New Page
                        </Link>
                    </div>
                </div>

                <div v-else class="overflow-hidden">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                        <thead class="bg-neutral-50 dark:bg-neutral-900">
                            <tr>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400"
                                >
                                    Title
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400"
                                >
                                    Slug
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400"
                                >
                                    Status
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400"
                                >
                                    Author
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400"
                                >
                                    Updated
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                            <tr
                                v-for="page in filteredPages"
                                :key="page.id"
                                class="hover:bg-neutral-50 dark:hover:bg-neutral-700"
                            >
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm font-medium text-neutral-900 dark:text-white">
                                        {{ page.title }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ page.slug }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        :class="getStatusBadgeClass(page.status)"
                                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                    >
                                        {{ page.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ page.author?.name || 'Unknown' }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ formatDate(page.updated_at) }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="`/admin/pages/${page.id}/edit`"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deletePage(page.id)"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="pages.last_page > 1"
                    class="border-t border-neutral-200 bg-white px-4 py-3 dark:border-neutral-700 dark:bg-neutral-800 sm:px-6"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                :href="`/admin/pages?page=${pages.current_page - 1}`"
                                :class="[
                                    pages.current_page === 1
                                        ? 'pointer-events-none opacity-50'
                                        : '',
                                    'relative inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50',
                                ]"
                            >
                                Previous
                            </Link>
                            <Link
                                :href="`/admin/pages?page=${pages.current_page + 1}`"
                                :class="[
                                    pages.current_page === pages.last_page
                                        ? 'pointer-events-none opacity-50'
                                        : '',
                                    'relative ml-3 inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50',
                                ]"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-neutral-700 dark:text-neutral-300">
                                    Showing
                                    <span class="font-medium">
                                        {{ (pages.current_page - 1) * pages.per_page + 1 }}
                                    </span>
                                    to
                                    <span class="font-medium">
                                        {{
                                            Math.min(
                                                pages.current_page * pages.per_page,
                                                pages.total,
                                            )
                                        }}
                                    </span>
                                    of
                                    <span class="font-medium">{{ pages.total }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="inline-flex -space-x-px rounded-md shadow-sm">
                                    <Link
                                        :href="`/admin/pages?page=${pages.current_page - 1}`"
                                        :class="[
                                            pages.current_page === 1
                                                ? 'pointer-events-none opacity-50'
                                                : '',
                                            'relative inline-flex items-center rounded-l-md border border-neutral-300 bg-white px-2 py-2 text-sm font-medium text-neutral-500 hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
                                        ]"
                                    >
                                        Previous
                                    </Link>
                                    <Link
                                        :href="`/admin/pages?page=${pages.current_page + 1}`"
                                        :class="[
                                            pages.current_page === pages.last_page
                                                ? 'pointer-events-none opacity-50'
                                                : '',
                                            'relative inline-flex items-center rounded-r-md border border-neutral-300 bg-white px-2 py-2 text-sm font-medium text-neutral-500 hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
                                        ]"
                                    >
                                        Next
                                    </Link>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
