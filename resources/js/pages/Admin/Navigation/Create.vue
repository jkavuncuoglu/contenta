<template>
    <Head title="Create Menu" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2
                        class="text-2xl font-bold text-neutral-900 dark:text-white"
                    >
                        Create Menu
                    </h2>
                    <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                        Create a new navigation menu for your site
                    </p>
                </div>
                <Link
                    :href="'/admin/menus'"
                    class="inline-flex items-center rounded-lg bg-neutral-100 px-4 py-2 font-semibold text-neutral-700 transition-colors hover:bg-neutral-200 dark:bg-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600"
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
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        />
                    </svg>
                    Back to Menus
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-3xl">
                <form
                    @submit.prevent="submit"
                    class="rounded-lg border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-800"
                >
                    <div class="space-y-6 p-6">
                        <!-- Name -->
                        <div>
                            <label
                                for="name"
                                class="mb-2 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Menu Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="block py-2 px-3 w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 placeholder-neutral-400 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                                placeholder="e.g., Main Navigation"
                            />
                            <p
                                v-if="errors.name"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ errors.name }}
                            </p>
                            <p
                                class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                            >
                                A descriptive name for this menu (displayed in
                                admin only)
                            </p>
                        </div>

                        <!-- Slug -->
                        <div>
                            <label
                                for="slug"
                                class="mb-2 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Slug
                            </label>
                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="block py-2 px-3 w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 placeholder-neutral-400 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                                placeholder="Auto-generated from name"
                            />
                            <p
                                v-if="errors.slug"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ errors.slug }}
                            </p>
                            <p
                                class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                            >
                                URL-friendly identifier (auto-generated if left
                                empty)
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label
                                for="description"
                                class="mb-2 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Description
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 placeholder-neutral-400 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                                placeholder="Optional description of this menu's purpose"
                            />
                            <p
                                v-if="errors.description"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ errors.description }}
                            </p>
                        </div>

                        <!-- Location -->
                        <div>
                            <label
                                for="location"
                                class="mb-2 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Menu Location
                            </label>
                            <select
                                id="location"
                                v-model="form.location"
                                class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                            >
                                <option value="">None (Unassigned)</option>
                                <option
                                    v-for="(label, value) in locations"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                            <p
                                v-if="errors.location"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ errors.location }}
                            </p>
                            <p
                                class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                            >
                                Where this menu will be displayed on your site
                            </p>
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-center">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-neutral-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label
                                for="is_active"
                                class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300"
                            >
                                Active (display this menu on the site)
                            </label>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900"
                    >
                        <Link
                            :href="'/admin/menus'"
                            class="rounded-lg border border-neutral-300 bg-white px-4 py-2 font-medium text-neutral-700 transition-colors hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="processing"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-2 font-semibold text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <svg
                                v-if="processing"
                                class="mr-2 -ml-1 h-4 w-4 animate-spin text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            {{ processing ? 'Creating...' : 'Create Menu' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

interface Props {
    locations: Record<string, string>;
    errors?: Record<string, string>;
}

const props = withDefaults(defineProps<Props>(), {
    errors: () => ({}),
});

const form = reactive({
    name: '',
    slug: '',
    description: '',
    location: '',
    is_active: true,
});

const processing = ref(false);
const errors = ref(props.errors);

const submit = async () => {
    processing.value = true;
    errors.value = {};

    try {
        const response = await fetch('/admin/menus/api', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || '',
            },
            body: JSON.stringify(form),
        });

        if (response.ok) {
            const menu = await response.json();
            router.visit('/admin/menus/' + menu.id + '/edit');
        } else {
            const errorData = await response.json();
            errors.value = errorData.errors || {};
        }
    } catch (error) {
        console.error('Failed to create menu:', error);
        alert('Failed to create menu. Please try again.');
    } finally {
        processing.value = false;
    }
};
</script>
