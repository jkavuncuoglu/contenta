<template>
    <Head title="Categories" />
    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                    >
                        Create Category
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Add a new category to organize your content
                    </p>
                </div>
                <Link
                    href="/admin/categories"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-gray-700"
                >
                    <Icon icon="lucide:arrow-left" class="mr-2 h-4 w-4" />
                    Back to Categories
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
                            Category Details
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
                                placeholder="Enter category name"
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
                                placeholder="category-slug"
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
                                placeholder="Brief description of this category"
                            ></textarea>
                            <p
                                v-if="errors.description"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.description[0] }}
                            </p>
                        </div>

                        <!-- Parent Category -->
                        <div>
                            <label
                                for="parent_id"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Parent Category
                            </label>
                            <select
                                id="parent_id"
                                v-model="form.parent_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700"
                            >
                                <option value="">
                                    No Parent (Root Category)
                                </option>
                                <option
                                    v-for="category in availableParents"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                            </select>
                            <p
                                v-if="errors.parent_id"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.parent_id[0] }}
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
                                Choose a color to help identify this category
                            </p>
                            <p
                                v-if="errors.color"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.color[0] }}
                            </p>
                        </div>

                        <!-- Featured Image -->
                        <div>
                            <label
                                for="image"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Featured Image
                            </label>
                            <div class="mt-1">
                                <input
                                    id="image"
                                    ref="imageInput"
                                    type="file"
                                    accept="image/*"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-full file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
                                    @change="handleImageChange"
                                />
                            </div>
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                Upload an image to represent this category (max
                                2MB)
                            </p>
                            <p
                                v-if="errors.image"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.image[0] }}
                            </p>
                        </div>

                        <!-- Settings -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Featured -->
                            <div class="flex items-center">
                                <input
                                    id="is_featured"
                                    v-model="form.is_featured"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <label
                                    for="is_featured"
                                    class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Featured Category
                                </label>
                            </div>

                            <!-- Sort Order -->
                            <div>
                                <label
                                    for="sort_order"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Sort Order
                                </label>
                                <input
                                    id="sort_order"
                                    v-model.number="form.sort_order"
                                    type="number"
                                    min="0"
                                    max="9999"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700"
                                />
                                <p
                                    v-if="errors.sort_order"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.sort_order[0] }}
                                </p>
                            </div>
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
                        href="/admin/categories"
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
                        {{ isLoading ? 'Creating...' : 'Create Category' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useCategoriesStore } from '@/stores/categories';
import type { CategoryForm, CreateCategoryData } from '@/types';
import { Icon } from '@iconify/vue';
import { Head, router as inertia, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const categoriesStore = useCategoriesStore();

// Form state
const form = ref<CategoryForm>({
    name: '',
    slug: '',
    description: '',
    parent_id: undefined,
    color: '#3b82f6',
    meta_title: '',
    meta_description: '',
    is_featured: false,
    sort_order: 0,
    image: undefined,
});

const errors = ref<Record<string, string[]>>({});
const imageInput = ref<HTMLInputElement>();

// Computed properties
const { isLoading } = categoriesStore;

const availableParents = computed(() => {
    return categoriesStore.categories.filter((cat) => !cat.parent_id);
});

// Methods
const generateSlug = () => {
    if (!form.value.slug && form.value.name) {
        form.value.slug = form.value.name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }
};

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.value.image = target.files[0];
    }
};

const handleSubmit = async () => {
    errors.value = {};

    try {
        const data: CreateCategoryData = {
            name: form.value.name,
            slug: form.value.slug,
            description: form.value.description || undefined,
            parent_id: form.value.parent_id || undefined,
            color: form.value.color || undefined,
            meta_title: form.value.meta_title || undefined,
            meta_description: form.value.meta_description || undefined,
            is_featured: form.value.is_featured,
            sort_order: form.value.sort_order,
            image: form.value.image,
        };

        await categoriesStore.createCategory(data);
        inertia.get('/admin/categories');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }
    }
};

// Load categories for parent selection
onMounted(() => {
    categoriesStore.fetchCategories();
});
</script>
