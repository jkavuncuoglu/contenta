<template>
    <Head title="Tags" />
    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white"
                    >
                        Tags
                    </h1>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        Manage content tags and organize your posts
                    </p>
                </div>
                <Link
                    href="/admin/tags/create"
                    class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-blue-900"
                >
                    <Icon name="plus" class="mr-2 h-4 w-4" />
                    Add Tag
                </Link>
            </div>

            <!-- Filters and Search -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label
                            for="search"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >Search</label
                        >
                        <input
                            id="search"
                            v-model="filters.search"
                            type="text"
                            placeholder="Search tags..."
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700"
                            @input="debouncedSearch"
                        />
                    </div>

                    <div>
                        <label
                            for="popular"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >Filter</label
                        >
                        <select
                            id="popular"
                            v-model="filters.popular"
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700"
                            @change="handleFilterChange"
                        >
                            <option value="">All Tags</option>
                            <option :value="true">Popular Tags Only</option>
                            <option :value="false">Unused Tags Only</option>
                        </select>
                    </div>

                    <div>
                        <label
                            for="sort"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >Sort By</label
                        >
                        <select
                            id="sort"
                            v-model="filters.sort_by"
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700"
                            @change="handleFilterChange"
                        >
                            <option value="name">Name</option>
                            <option value="created_at">Date Created</option>
                            <option value="posts_count">Post Count</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div
                v-if="selectedTags.length > 0"
                class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20"
            >
                <div class="flex items-center justify-between">
                    <span
                        class="text-sm font-medium text-yellow-800 dark:text-yellow-200"
                    >
                        {{ selectedTags.length }} tag{{
                            selectedTags.length === 1 ? '' : 's'
                        }}
                        selected
                    </span>
                    <div class="flex items-center space-x-2">
                        <button
                            @click="showMergeModal = true"
                            class="inline-flex items-center rounded border border-transparent bg-blue-100 px-3 py-1 text-sm font-medium text-blue-700 hover:bg-blue-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                        >
                            <Icon name="arrow-path" class="mr-1 h-4 w-4" />
                            Merge
                        </button>
                        <button
                            @click="bulkDelete"
                            class="inline-flex items-center rounded border border-transparent bg-red-100 px-3 py-1 text-sm font-medium text-red-700 hover:bg-red-200 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none"
                        >
                            <Icon name="trash" class="mr-1 h-4 w-4" />
                            Delete
                        </button>
                        <button
                            @click="clearSelection"
                            class="inline-flex items-center rounded border border-neutral-300 bg-white px-3 py-1 text-sm font-medium text-neutral-700 hover:bg-neutral-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tags List -->
            <div class="rounded-lg bg-white shadow dark:bg-neutral-800">
                <div
                    class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700"
                >
                    <div class="flex items-center justify-between">
                        <h3
                            class="text-lg font-medium text-neutral-900 dark:text-white"
                        >
                            Tags ({{ totalTags }})
                        </h3>
                        <div class="flex items-center space-x-2">
                            <button
                                v-if="hasTags"
                                @click="selectAll"
                                class="text-sm text-indigo-600 hover:underline dark:text-indigo-400"
                            >
                                {{
                                    selectedTags.length === tags.length
                                        ? 'Deselect All'
                                        : 'Select All'
                                }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden">
                    <div
                        v-if="isLoading"
                        class="flex items-center justify-center py-12"
                    >
                        <div
                            class="h-8 w-8 animate-spin rounded-full border-b-2 border-blue-600"
                        ></div>
                    </div>

                    <div v-else-if="hasError" class="p-6 text-center">
                        <p class="text-red-600 dark:text-red-400">
                            {{ error }}
                        </p>
                        <button
                            @click="loadTags"
                            class="mt-2 text-sm text-blue-600 hover:underline dark:text-blue-400"
                        >
                            Try again
                        </button>
                    </div>

                    <div v-else-if="!hasTags" class="p-12 text-center">
                        <Icon
                            name="tag"
                            class="mx-auto h-12 w-12 text-neutral-400"
                        />
                        <h3
                            class="mt-2 text-sm font-medium text-neutral-900 dark:text-white"
                        >
                            No tags
                        </h3>
                        <p
                            class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                        >
                            Get started by creating a new tag.
                        </p>
                        <div class="mt-6">
                            <Link
                                href="/admin/tags/create"
                                class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-700"
                            >
                                <Icon name="plus" class="mr-2 h-4 w-4" />
                                Add Tag
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
                                        <th class="px-6 py-3 text-left">
                                            <input
                                                type="checkbox"
                                                :checked="
                                                    selectedTags.length ===
                                                        tags.length &&
                                                    tags.length > 0
                                                "
                                                :indeterminate="
                                                    selectedTags.length > 0 &&
                                                    selectedTags.length <
                                                        tags.length
                                                "
                                                class="h-4 w-4 rounded border-neutral-300 text-indigo-600 focus:ring-indigo-500"
                                                @change="toggleSelectAll"
                                            />
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium tracking-wider text-neutral-500 uppercase dark:text-neutral-400"
                                        >
                                            Tag
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium tracking-wider text-neutral-500 uppercase dark:text-neutral-400"
                                        >
                                            Slug
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
                                        v-for="tag in tags"
                                        :key="tag.id"
                                        class="hover:bg-neutral-50 dark:hover:bg-neutral-700"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input
                                                type="checkbox"
                                                :checked="
                                                    selectedTags.includes(
                                                        tag.id,
                                                    )
                                                "
                                                class="h-4 w-4 rounded border-neutral-300 text-indigo-600 focus:ring-indigo-500"
                                                @change="toggleTag(tag.id)"
                                            />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    v-if="tag.color"
                                                    class="mr-3 h-4 w-4 rounded border border-neutral-300"
                                                    :style="{
                                                        backgroundColor:
                                                            tag.color,
                                                    }"
                                                ></div>
                                                <div>
                                                    <div
                                                        class="text-sm font-medium text-neutral-900 dark:text-white"
                                                    >
                                                        {{ tag.name }}
                                                    </div>
                                                    <div
                                                        v-if="tag.description"
                                                        class="max-w-xs truncate text-sm text-neutral-500 dark:text-neutral-400"
                                                    >
                                                        {{ tag.description }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm whitespace-nowrap text-neutral-500 dark:text-neutral-400"
                                        >
                                            {{ tag.slug }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm whitespace-nowrap text-neutral-500 dark:text-neutral-400"
                                        >
                                            {{ tag.posts_count || 0 }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm whitespace-nowrap text-neutral-500 dark:text-neutral-400"
                                        >
                                            {{ formatDate(tag.created_at) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                                        >
                                            <div
                                                class="flex items-center justify-end space-x-2"
                                            >
                                                <Link
                                                    :href="`/admin/tags/${tag.id}/edit`"
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                >
                                                    <Icon
                                                        name="pencil"
                                                        class="h-4 w-4"
                                                    />
                                                </Link>
                                                <button
                                                    @click="deleteTag(tag)"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                >
                                                    <Icon
                                                        name="trash"
                                                        class="h-4 w-4"
                                                    />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div
                            v-if="pagination.last_page > 1"
                            class="border-t border-neutral-200 bg-white px-4 py-3 sm:px-6 dark:border-neutral-700 dark:bg-neutral-800"
                        >
                            <div class="flex items-center justify-between">
                                <div
                                    class="flex flex-1 justify-between sm:hidden"
                                >
                                    <button
                                        :disabled="
                                            pagination.current_page === 1
                                        "
                                        @click="
                                            changePage(
                                                pagination.current_page - 1,
                                            )
                                        "
                                        class="relative inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        Previous
                                    </button>
                                    <button
                                        :disabled="
                                            pagination.current_page ===
                                            pagination.last_page
                                        "
                                        @click="
                                            changePage(
                                                pagination.current_page + 1,
                                            )
                                        "
                                        class="relative ml-3 inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        Next
                                    </button>
                                </div>
                                <div
                                    class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between"
                                >
                                    <div>
                                        <p
                                            class="text-sm text-neutral-700 dark:text-neutral-300"
                                        >
                                            Showing
                                            <span class="font-medium">{{
                                                pagination.from
                                            }}</span>
                                            to
                                            <span class="font-medium">{{
                                                pagination.to
                                            }}</span>
                                            of
                                            <span class="font-medium">{{
                                                pagination.total
                                            }}</span>
                                            results
                                        </p>
                                    </div>
                                    <div>
                                        <nav
                                            class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm"
                                            aria-label="Pagination"
                                        >
                                            <button
                                                :disabled="
                                                    pagination.current_page ===
                                                    1
                                                "
                                                @click="
                                                    changePage(
                                                        pagination.current_page -
                                                            1,
                                                    )
                                                "
                                                class="relative inline-flex items-center rounded-l-md border border-neutral-300 bg-white px-2 py-2 text-sm font-medium text-neutral-500 hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                                Previous
                                            </button>
                                            <button
                                                v-for="page in visiblePages"
                                                :key="page"
                                                @click="changePage(page)"
                                                :class="[
                                                    page ===
                                                    pagination.current_page
                                                        ? 'z-10 border-indigo-500 bg-indigo-50 text-indigo-600'
                                                        : 'border-neutral-300 bg-white text-neutral-500 hover:bg-neutral-50',
                                                    'relative inline-flex items-center border px-4 py-2 text-sm font-medium',
                                                ]"
                                            >
                                                {{ page }}
                                            </button>
                                            <button
                                                :disabled="
                                                    pagination.current_page ===
                                                    pagination.last_page
                                                "
                                                @click="
                                                    changePage(
                                                        pagination.current_page +
                                                            1,
                                                    )
                                                "
                                                class="relative inline-flex items-center rounded-r-md border border-neutral-300 bg-white px-2 py-2 text-sm font-medium text-neutral-500 hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                                Next
                                            </button>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Merge Modal -->
            <div
                v-if="showMergeModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title"
                role="dialog"
                aria-modal="true"
            >
                <div
                    class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0"
                >
                    <div
                        class="bg-opacity-75 fixed inset-0 bg-neutral-500 transition-opacity"
                        aria-hidden="true"
                        @click="showMergeModal = false"
                    ></div>
                    <div
                        class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle dark:bg-neutral-800"
                    >
                        <div
                            class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-neutral-800"
                        >
                            <h3
                                class="text-lg leading-6 font-medium text-neutral-900 dark:text-white"
                                id="modal-title"
                            >
                                Merge Tags
                            </h3>
                            <div class="mt-4">
                                <p
                                    class="text-sm text-neutral-500 dark:text-neutral-400"
                                >
                                    Select a target tag to merge all selected
                                    tags into:
                                </p>
                                <select
                                    v-model="mergeTargetId"
                                    class="mt-2 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700"
                                >
                                    <option value="">
                                        Select target tag...
                                    </option>
                                    <option
                                        v-for="tag in availableMergeTags"
                                        :key="tag.id"
                                        :value="tag.id"
                                    >
                                        {{ tag.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div
                            class="bg-neutral-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-neutral-700"
                        >
                            <button
                                @click="performMerge"
                                :disabled="!mergeTargetId"
                                class="inline-flex w-full justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Merge Tags
                            </button>
                            <button
                                @click="showMergeModal = false"
                                class="mt-3 inline-flex w-full justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-base font-medium text-neutral-700 shadow-sm hover:bg-neutral-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:border-neutral-500 dark:bg-neutral-600 dark:text-neutral-300 dark:hover:bg-neutral-500"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTagsStore } from '@/stores/tags';
import { Head, Link } from '@inertiajs/vue3';
import { computed, defineProps, onMounted, ref } from 'vue';

// replaced lodash-es debounce with a small local implementation
function debounce<F extends (...args: any[]) => any>(fn: F, wait = 200) {
    let t: number | undefined;
    return (...args: Parameters<F>) => {
        if (t !== undefined) {
            clearTimeout(t);
        }
        t = window.setTimeout(() => fn(...args), wait);
    };
}

import type { Tag, TagFilters } from '@/types';

const props = defineProps({
    tags: {
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

const tagsStore = useTagsStore();

// Reactive state
const selectedTags = ref<number[]>([]);
const showMergeModal = ref(false);
const mergeTargetId = ref<number | ''>('');
const filters = ref<TagFilters>({
    search: '',
    popular: undefined,
    sort_by: 'name',
    per_page: 15,
    page: 1,
});

// Computed properties
const { isLoading, hasError, error: tagError, hasTags, totalTags, pagination } =
    tagsStore;

const visiblePages = computed(() => {
    const current = pagination.current_page;
    const last = pagination.last_page;
    const delta = 2;
    const range = [];
    const rangeWithDots = [];

    for (
        let i = Math.max(2, current - delta);
        i <= Math.min(last - 1, current + delta);
        i++
    ) {
        range.push(i);
    }

    if (current - delta > 2) {
        rangeWithDots.push(1, '...');
    } else {
        rangeWithDots.push(1);
    }

    rangeWithDots.push(...range);

    if (current + delta < last - 1) {
        rangeWithDots.push('...', last);
    } else {
        rangeWithDots.push(last);
    }

    return rangeWithDots.filter(
        (item, index, arr) => arr.indexOf(item) === index,
    );
});

const availableMergeTags = computed(() => {
    return tags.value.filter((tag) => !selectedTags.value.includes(tag.id));
});

// Methods
const debouncedSearch = debounce(() => {
    filters.value.page = 1;
    loadTags();
}, 300);

const handleFilterChange = () => {
    filters.value.page = 1;
    loadTags();
};

const changePage = (page: number) => {
    filters.value.page = page;
    loadTags();
};

const loadTags = async () => {
    await tagsStore.fetchTags(filters.value);
};

const toggleTag = (tagId: number) => {
    const index = selectedTags.value.indexOf(tagId);
    if (index > -1) {
        selectedTags.value.splice(index, 1);
    } else {
        selectedTags.value.push(tagId);
    }
};

const toggleSelectAll = () => {
    if (selectedTags.value.length === tags.value.length) {
        selectedTags.value = [];
    } else {
        selectedTags.value = tags.value.map((tag) => tag.id);
    }
};

const selectAll = () => {
    if (selectedTags.value.length === tags.value.length) {
        selectedTags.value = [];
    } else {
        selectedTags.value = tags.value.map((tag) => tag.id);
    }
};

const clearSelection = () => {
    selectedTags.value = [];
};

const deleteTag = async (tag: Tag) => {
    if (confirm(`Are you sure you want to delete "${tag.name}"?`)) {
        try {
            await tagsStore.deleteTag(tag.id);
            await loadTags();
        } catch (error) {
            console.error('Failed to delete tag:', error);
        }
    }
};

const bulkDelete = async () => {
    if (
        confirm(
            `Are you sure you want to delete ${selectedTags.value.length} tag${selectedTags.value.length === 1 ? '' : 's'}?`,
        )
    ) {
        try {
            await tagsStore.bulkAction('delete', selectedTags.value);
            selectedTags.value = [];
            await loadTags();
        } catch (error) {
            console.error('Failed to delete tags:', error);
        }
    }
};

const performMerge = async () => {
    if (!mergeTargetId.value) return;

    try {
        await tagsStore.bulkAction(
            'merge',
            selectedTags.value,
            mergeTargetId.value as number,
        );
        selectedTags.value = [];
        showMergeModal.value = false;
        mergeTargetId.value = '';
        await loadTags();
    } catch (error) {
        console.error('Failed to merge tags:', error);
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

// Lifecycle
onMounted(() => {
    loadTags();
});
</script>
