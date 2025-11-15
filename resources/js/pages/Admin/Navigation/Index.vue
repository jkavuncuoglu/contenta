<template>
    <Head title="Menus" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2
                        class="text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        Menus
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Manage your site's navigation menus
                    </p>
                </div>
                <Link
                    :href="'/admin/menus/create'"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition-colors hover:bg-blue-700"
                >
                    <svg
                        class="mr-2 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    Create Menu
                </Link>
            </div>
        </template>

        <div class="py-6">
            <!-- Search and Filters -->
            <div class="mb-6 flex items-center gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                        >
                            <svg
                                class="h-5 w-5 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search menus..."
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2 pr-3 pl-10 text-gray-900 placeholder-gray-400 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                </div>
                <select
                    v-model="filterLocation"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">All Locations</option>
                    <option
                        v-for="(label, value) in locations"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
            </div>

            <!-- Menus Grid -->
            <div
                v-if="filteredMenus.length > 0"
                class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="menu in filteredMenus"
                    :key="menu.id"
                    class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="p-6">
                        <div class="mb-4 flex items-start justify-between">
                            <div class="flex-1">
                                <h3
                                    class="mb-1 text-lg font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ menu.name }}
                                </h3>
                                <p
                                    v-if="menu.description"
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                >
                                    {{ menu.description }}
                                </p>
                            </div>
                            <span
                                v-if="menu.is_active"
                                class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                            >
                                Active
                            </span>
                        </div>

                        <div class="mb-4 space-y-2">
                            <div class="flex items-center text-sm">
                                <svg
                                    class="mr-2 h-4 w-4 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                    />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{
                                        menu.location
                                            ? locations[menu.location]
                                            : 'No location'
                                    }}
                                </span>
                            </div>
                            <div class="flex items-center text-sm">
                                <svg
                                    class="mr-2 h-4 w-4 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ menu.items_count }}
                                    {{
                                        menu.items_count === 1
                                            ? 'item'
                                            : 'items'
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Link
                                :href="'/admin/menus/' + menu.id + '/edit'"
                                class="inline-flex flex-1 items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
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
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    />
                                </svg>
                                Edit
                            </Link>
                            <button
                                @click="duplicateMenu(menu)"
                                class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                title="Duplicate"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                    />
                                </svg>
                            </button>
                            <button
                                @click="confirmDelete(menu)"
                                class="rounded-lg bg-red-100 px-3 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-200 dark:bg-red-900 dark:text-red-300 dark:hover:bg-red-800"
                                title="Delete"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-12 text-center">
                <svg
                    class="mx-auto h-12 w-12 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"
                    />
                </svg>
                <h3
                    class="mt-2 text-sm font-medium text-gray-900 dark:text-white"
                >
                    No menus found
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{
                        search || filterLocation
                            ? 'Try adjusting your filters'
                            : 'Get started by creating a new menu'
                    }}
                </p>
                <div v-if="!search && !filterLocation" class="mt-6">
                    <Link
                        :href="'/admin/menus/create'"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition-colors hover:bg-blue-700"
                    >
                        <svg
                            class="mr-2 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                        Create Your First Menu
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Menu {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    location: string | null;
    is_active: boolean;
    items_count: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    menus: Menu[];
    locations: Record<string, string>;
}

const props = defineProps<Props>();

const search = ref('');
const filterLocation = ref('');

const filteredMenus = computed(() => {
    let filtered = props.menus;

    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(
            (menu) =>
                menu.name.toLowerCase().includes(searchLower) ||
                menu.description?.toLowerCase().includes(searchLower),
        );
    }

    if (filterLocation.value) {
        filtered = filtered.filter(
            (menu) => menu.location === filterLocation.value,
        );
    }

    return filtered;
});

const duplicateMenu = async (menu: Menu) => {
    const newName = prompt(
        `Enter a name for the duplicate menu:`,
        `${menu.name} (Copy)`,
    );
    if (!newName) return;

    try {
        const response = await fetch(
            '/admin/menus/api/' + menu.id + '/duplicate',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                },
                body: JSON.stringify({ name: newName }),
            },
        );

        if (response.ok) {
            router.reload();
        } else {
            alert('Failed to duplicate menu');
        }
    } catch {
        alert('Failed to duplicate menu');
    }
};

const confirmDelete = async (menu: Menu) => {
    if (
        !confirm(
            `Are you sure you want to delete "${menu.name}"? This will also delete all menu items.`,
        )
    ) {
        return;
    }

    try {
        const response = await fetch('/admin/menus/api/' + menu.id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || '',
            },
        });

        if (response.ok) {
            router.reload();
        } else {
            alert('Failed to delete menu');
        }
    } catch {
        alert('Failed to delete menu');
    }
};
</script>
