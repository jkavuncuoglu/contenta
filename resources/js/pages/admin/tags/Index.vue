<template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Tags</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Manage content tags and organize your posts
                    </p>
                </div>
                <Link
                    href="/admin/tags/create"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <Icon name="plus" class="w-4 h-4 mr-2" />
                    Add Tag
                </Link>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="search"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                        <input
                            id="search"
                            v-model="filters.search"
                            type="text"
                            placeholder="Search tags..."
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            @input="debouncedSearch"
                        />
                    </div>

                    <div>
                        <label for="popular"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filter</label>
                        <select
                            id="popular"
                            v-model="filters.popular"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            @change="handleFilterChange"
                        >
                            <option value="">All Tags</option>
                            <option :value="true">Popular Tags Only</option>
                            <option :value="false">Unused Tags Only</option>
                        </select>
                    </div>

                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort
                            By</label>
                        <select
                            id="sort"
                            v-model="filters.sort_by"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
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
            <div v-if="selectedTags.length > 0"
                 class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
          {{ selectedTags.length }} tag{{ selectedTags.length === 1 ? '' : 's' }} selected
        </span>
                    <div class="flex items-center space-x-2">
                        <button
                            @click="showMergeModal = true"
                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <Icon name="arrow-path" class="w-4 h-4 mr-1" />
                            Merge
                        </button>
                        <button
                            @click="bulkDelete"
                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            <Icon name="trash" class="w-4 h-4 mr-1" />
                            Delete
                        </button>
                        <button
                            @click="clearSelection"
                            class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tags List -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Tags ({{ totalTags }})
                        </h3>
                        <div class="flex items-center space-x-2">
                            <button
                                v-if="hasTags"
                                @click="selectAll"
                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
                            >
                                {{ selectedTags.length === tags.length ? 'Deselect All' : 'Select All' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden">
                    <div v-if="isLoading" class="flex items-center justify-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>

                    <div v-else-if="hasError" class="p-6 text-center">
                        <p class="text-red-600 dark:text-red-400">{{ error }}</p>
                        <button
                            @click="loadTags"
                            class="mt-2 text-sm text-blue-600 dark:text-blue-400 hover:underline"
                        >
                            Try again
                        </button>
                    </div>

                    <div v-else-if="!hasTags" class="p-12 text-center">
                        <Icon name="tag" class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tags</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new tag.</p>
                        <div class="mt-6">
                            <Link
                                href="/admin/tags/create"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                            >
                                <Icon name="plus" class="w-4 h-4 mr-2" />
                                Add Tag
                            </Link>
                        </div>
                    </div>

                    <div v-else>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left">
                                        <input
                                            type="checkbox"
                                            :checked="selectedTags.length === tags.length && tags.length > 0"
                                            :indeterminate="selectedTags.length > 0 && selectedTags.length < tags.length"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            @change="toggleSelectAll"
                                        />
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Tag
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Slug
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Posts
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Created
                                    </th>
                                    <th class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="tag in tags" :key="tag.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input
                                            type="checkbox"
                                            :checked="selectedTags.includes(tag.id)"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            @change="toggleTag(tag.id)"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                v-if="tag.color"
                                                class="w-4 h-4 rounded mr-3 border border-gray-300"
                                                :style="{ backgroundColor: tag.color }"
                                            ></div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ tag.name }}
                                                </div>
                                                <div v-if="tag.description"
                                                     class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                                    {{ tag.description }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ tag.slug }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ tag.posts_count || 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(tag.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Link
                                                :href="`/admin/tags/${tag.id}/edit`"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            >
                                                <Icon name="pencil" class="w-4 h-4" />
                                            </Link>
                                            <button
                                                @click="deleteTag(tag)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            >
                                                <Icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="pagination.last_page > 1"
                             class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    <button
                                        :disabled="pagination.current_page === 1"
                                        @click="changePage(pagination.current_page - 1)"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Previous
                                    </button>
                                    <button
                                        :disabled="pagination.current_page === pagination.last_page"
                                        @click="changePage(pagination.current_page + 1)"
                                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Next
                                    </button>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            Showing
                                            <span class="font-medium">{{ pagination.from }}</span>
                                            to
                                            <span class="font-medium">{{ pagination.to }}</span>
                                            of
                                            <span class="font-medium">{{ pagination.total }}</span>
                                            results
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                             aria-label="Pagination">
                                            <button
                                                :disabled="pagination.current_page === 1"
                                                @click="changePage(pagination.current_page - 1)"
                                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                Previous
                                            </button>
                                            <button
                                                v-for="page in visiblePages"
                                                :key="page"
                                                @click="changePage(page)"
                                                :class="[
                        page === pagination.current_page
                          ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                      ]"
                                            >
                                                {{ page }}
                                            </button>
                                            <button
                                                :disabled="pagination.current_page === pagination.last_page"
                                                @click="changePage(pagination.current_page + 1)"
                                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
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
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                         @click="showMergeModal = false"></div>
                    <div
                        class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Merge Tags
                            </h3>
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Select a target tag to merge all selected tags into:
                                </p>
                                <select
                                    v-model="mergeTargetId"
                                    class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="">Select target tag...</option>
                                    <option v-for="tag in availableMergeTags" :key="tag.id" :value="tag.id">
                                        {{ tag.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                @click="performMerge"
                                :disabled="!mergeTargetId"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Merge Tags
                            </button>
                            <button
                                @click="showMergeModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-500"
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
import { ref, computed, onMounted } from 'vue';
import { useTagsStore } from '@/stores/tags';
import Icon from '@/components/Icon.vue';
import { Link } from '@inertiajs/vue3';

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
    page: 1
});

// Computed properties
const {
    tags,
    isLoading,
    hasError,
    error,
    hasTags,
    totalTags,
    pagination
} = tagsStore;

const visiblePages = computed(() => {
    const current = pagination.current_page;
    const last = pagination.last_page;
    const delta = 2;
    const range = [];
    const rangeWithDots = [];

    for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
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

    return rangeWithDots.filter((item, index, arr) => arr.indexOf(item) === index);
});

const availableMergeTags = computed(() => {
    return tags.value.filter(tag => !selectedTags.value.includes(tag.id));
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
        selectedTags.value = tags.value.map(tag => tag.id);
    }
};

const selectAll = () => {
    if (selectedTags.value.length === tags.value.length) {
        selectedTags.value = [];
    } else {
        selectedTags.value = tags.value.map(tag => tag.id);
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
    if (confirm(`Are you sure you want to delete ${selectedTags.value.length} tag${selectedTags.value.length === 1 ? '' : 's'}?`)) {
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
        await tagsStore.bulkAction('merge', selectedTags.value, mergeTargetId.value as number);
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
