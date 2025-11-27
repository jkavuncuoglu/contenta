<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

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
        title: 'Layouts',
        href: '/admin/page-builder/layouts',
    },
    {
        title: 'Create Layout',
        href: '/admin/page-builder/layouts/create',
    },
];

const creating = ref(false);
const areas = ref(['main']);

const form = reactive({
    name: '',
    slug: '',
    description: '',
    is_active: true,
    settings: {
        container_width: 'full',
        spacing: 'normal',
    },
});

// Auto-generate slug from name
watch(
    () => form.name,
    (newName) => {
        if (newName && !form.slug) {
            form.slug = newName
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        }
    },
);

const addArea = () => {
    areas.value.push('');
};

const removeArea = (index: number) => {
    if (areas.value.length > 1) {
        areas.value.splice(index, 1);
    }
};

const createLayout = async () => {
    try {
        creating.value = true;

        const structure = {
            areas: areas.value.filter((area) => area.trim()),
            settings: form.settings,
        };

        const response = await fetch('/admin/page-builder/api/layouts', {
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
            body: JSON.stringify({
                name: form.name,
                slug: form.slug,
                description: form.description,
                is_active: form.is_active,
                structure: structure,
            }),
        });

        if (response.ok) {
            router.visit('/admin/page-builder/layouts');
        } else {
            const errors = await response.json();
            console.error('Failed to create layout:', errors);
            alert(
                'Failed to create layout. Please check the form and try again.',
            );
        }
    } catch (error) {
        console.error('Error creating layout:', error);
        alert('Failed to create layout. Please try again.');
    } finally {
        creating.value = false;
    }
};

const goBack = () => {
    router.visit('/admin/page-builder/layouts');
};
</script>

<template>
    <Head title="Create Layout - Pages" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Page header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-semibold text-gray-900 dark:text-white"
                    >
                        Create New Layout
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Create a new page layout template for the Pages
                    </p>
                </div>
            </div>

            <!-- Form -->
            <div class="max-w-4xl">
                <form @submit.prevent="createLayout" class="space-y-8">
                    <!-- Basic Information -->
                    <div
                        class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <h3
                            class="mb-6 text-lg font-medium text-gray-900 dark:text-white"
                        >
                            Basic Information
                        </h3>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label
                                    for="name"
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Layout Name *
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="e.g., Landing Page Layout"
                                />
                            </div>

                            <div>
                                <label
                                    for="slug"
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Layout Slug *
                                </label>
                                <input
                                    id="slug"
                                    v-model="form.slug"
                                    type="text"
                                    required
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="e.g., landing-page"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <label
                                    for="description"
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Description
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Describe when to use this layout..."
                                ></textarea>
                            </div>

                            <div class="flex items-center">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label
                                    for="is_active"
                                    class="ml-2 block text-sm text-gray-700 dark:text-gray-300"
                                >
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Layout Structure -->
                    <div
                        class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <div class="mb-6 flex items-center justify-between">
                            <h3
                                class="text-lg font-medium text-gray-900 dark:text-white"
                            >
                                Layout Structure
                            </h3>
                            <button
                                type="button"
                                @click="addArea"
                                class="inline-flex items-center rounded-md border border-transparent bg-blue-100 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-200 focus:ring-2 focus:ring-blue-500 focus:outline-none"
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
                                Add Area
                            </button>
                        </div>

                        <!-- Areas -->
                        <div class="mb-6 space-y-4">
                            <h4
                                class="text-md font-medium text-gray-900 dark:text-white"
                            >
                                Content Areas
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Define the content areas where blocks can be
                                placed in this layout.
                            </p>

                            <div class="space-y-3">
                                <div
                                    v-for="(area, index) in areas"
                                    :key="index"
                                    class="flex items-center space-x-3"
                                >
                                    <div class="flex-1">
                                        <input
                                            v-model="areas[index]"
                                            type="text"
                                            required
                                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            :placeholder="`Area ${index + 1} (e.g., header, main, sidebar, footer)`"
                                        />
                                    </div>
                                    <button
                                        v-if="areas.length > 1"
                                        type="button"
                                        @click="removeArea(index)"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            ></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Layout Settings -->
                        <div
                            class="border-t border-gray-200 pt-6 dark:border-gray-600"
                        >
                            <h4
                                class="text-md mb-4 font-medium text-gray-900 dark:text-white"
                            >
                                Layout Settings
                            </h4>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label
                                        for="container_width"
                                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Container Width
                                    </label>
                                    <select
                                        id="container_width"
                                        v-model="form.settings.container_width"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="full">Full Width</option>
                                        <option value="container">
                                            Container
                                        </option>
                                        <option value="narrow">Narrow</option>
                                        <option value="wide">Wide</option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        for="spacing"
                                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Spacing
                                    </label>
                                    <select
                                        id="spacing"
                                        v-model="form.settings.spacing"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="tight">Tight</option>
                                        <option value="normal">Normal</option>
                                        <option value="loose">Loose</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Layout Preview -->
                    <div
                        class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <h3
                            class="mb-6 text-lg font-medium text-gray-900 dark:text-white"
                        >
                            Layout Preview
                        </h3>

                        <div
                            class="rounded-lg border-2 border-dashed border-gray-300 p-4 dark:border-gray-600"
                        >
                            <div class="space-y-4">
                                <div
                                    v-for="area in areas.filter((a) =>
                                        a.trim(),
                                    )"
                                    :key="area"
                                    class="rounded-lg border-2 border-blue-200 bg-blue-50 p-4 text-center dark:border-blue-700 dark:bg-blue-900/20"
                                >
                                    <div
                                        class="font-medium text-blue-800 dark:text-blue-200"
                                    >
                                        {{ area }}
                                    </div>
                                    <div
                                        class="mt-1 text-sm text-blue-600 dark:text-blue-300"
                                    >
                                        Content Area
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p
                            class="mt-4 text-sm text-gray-600 dark:text-gray-400"
                        >
                            This is a preview of how your layout areas will be
                            structured. Content blocks will be placed in these
                            areas.
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="goBack"
                            class="rounded-md border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="
                                !form.name ||
                                !form.slug ||
                                areas.filter((a) => a.trim()).length === 0 ||
                                creating
                            "
                            class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ creating ? 'Creating...' : 'Create Layout' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
