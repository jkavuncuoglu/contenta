<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h4 class="text-sm font-semibold text-neutral-900 dark:text-white">
                Add Menu Items
            </h4>
            <button
                @click="$emit('close')"
                class="text-neutral-400 hover:text-neutral-500 dark:hover:text-neutral-300"
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
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <!-- Tabs -->
        <div class="border-b border-neutral-200 dark:border-neutral-700">
            <nav class="-mb-px flex space-x-8">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="border-b-2 px-1 pb-2 text-sm font-medium transition-colors"
                    :class="{
                        'border-blue-500 text-blue-600 dark:text-blue-400':
                            activeTab === tab.id,
                        'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300':
                            activeTab !== tab.id,
                    }"
                >
                    {{ tab.label }}
                </button>
            </nav>
        </div>

        <!-- Custom Link Form -->
        <div v-if="activeTab === 'custom'" class="space-y-4">
            <div>
                <label
                    for="custom-title"
                    class="mb-2 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                >
                    Link Text
                </label>
                <input
                    id="custom-title"
                    v-model="customLink.title"
                    type="text"
                    placeholder="Home"
                    class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                />
            </div>
            <div>
                <label
                    for="custom-url"
                    class="mb-2 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                >
                    URL
                </label>
                <input
                    id="custom-url"
                    v-model="customLink.url"
                    type="text"
                    placeholder="/about"
                    class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                />
            </div>
            <button
                @click="addCustomLink"
                :disabled="!customLink.title || !customLink.url"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                Add to Menu
            </button>
        </div>

        <!-- Pages/Posts/Categories/Tags -->
        <div v-else class="space-y-4">
            <!-- Search -->
            <div>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search..."
                    class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                />
            </div>

            <!-- Items List -->
            <div class="max-h-64 space-y-2 overflow-y-auto">
                <!-- Loading State -->
                <div v-if="loading" class="py-8 text-center">
                    <div
                        class="inline-block h-8 w-8 animate-spin rounded-full border-b-2 border-blue-600"
                    ></div>
                    <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                        Loading...
                    </p>
                </div>

                <!-- Items -->
                <template v-else>
                    <label
                        v-for="item in filteredItems"
                        :key="item.id"
                        class="flex cursor-pointer items-center rounded-lg border border-neutral-200 p-3 transition-colors hover:bg-neutral-50 dark:border-neutral-700 dark:hover:bg-neutral-700"
                    >
                        <input
                            v-model="selectedItems"
                            type="checkbox"
                            :value="item.id"
                            class="h-4 w-4 rounded border-neutral-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span
                            class="ml-3 text-sm text-neutral-900 dark:text-white"
                        >
                            {{ item.title || item.name }}
                        </span>
                    </label>

                    <div
                        v-if="filteredItems.length === 0"
                        class="py-8 text-center text-neutral-500 dark:text-neutral-400"
                    >
                        <p class="text-sm">No items found</p>
                    </div>
                </template>
            </div>

            <!-- Add Selected Button -->
            <button
                @click="addSelectedItems"
                :disabled="selectedItems.length === 0 || adding"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                {{
                    adding
                        ? 'Adding...'
                        : `Add ${selectedItems.length} Selected`
                }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';

interface SourceItem {
    id: number;
    title?: string;
    name?: string;
    slug?: string;
    status?: string;
}

interface Props {
    menuId: number;
}

interface Emits {
    (e: 'items-added', items: any[]): void;
    (e: 'close'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const activeTab = ref('custom');
const search = ref('');
const selectedItems = ref<number[]>([]);
const customLink = ref({ title: '', url: '' });
const adding = ref(false);
const loading = ref(false);

const tabs = [
    { id: 'custom', label: 'Custom Link' },
    { id: 'page', label: 'Pages' },
    { id: 'post', label: 'Posts' },
    { id: 'category', label: 'Categories' },
    { id: 'tag', label: 'Tags' },
];

const items = ref<SourceItem[]>([]);

const filteredItems = computed(() => {
    if (!search.value) return items.value;
    const searchLower = search.value.toLowerCase();
    return items.value.filter(
        (item) =>
            item.title?.toLowerCase().includes(searchLower) ||
            item.name?.toLowerCase().includes(searchLower),
    );
});

const fetchItems = async (type: string) => {
    if (type === 'custom') {
        items.value = [];
        return;
    }

    loading.value = true;
    try {
        let endpoint = '';

        switch (type) {
            case 'page':
                endpoint = '/admin/pages/api';
                break;
            case 'post':
                endpoint = '/admin/api/posts';
                break;
            case 'category':
                endpoint = '/admin/api/categories';
                break;
            case 'tag':
                endpoint = '/admin/api/tags';
                break;
        }

        const response = await fetch(endpoint + '?per_page=50', {
            headers: {
                Accept: 'application/json',
            },
        });

        if (response.ok) {
            const result = await response.json();
            // Handle both paginated responses and direct arrays
            if (result.data) {
                items.value = Array.isArray(result.data) ? result.data : [];
            } else {
                items.value = Array.isArray(result) ? result : [];
            }
        } else {
            console.error('Failed to fetch items:', response.statusText);
            items.value = [];
        }
    } catch (error) {
        console.error('Failed to fetch items:', error);
        items.value = [];
    } finally {
        loading.value = false;
    }
};

// Watch for tab changes and fetch items
watch(
    activeTab,
    (newTab) => {
        selectedItems.value = [];
        search.value = '';
        fetchItems(newTab);
    },
    { immediate: true },
);

const addCustomLink = async () => {
    adding.value = true;
    try {
        const response = await fetch(
            '/admin/menus/api/' + props.menuId + '/items',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    type: 'custom',
                    title: customLink.value.title,
                    url: customLink.value.url,
                    target: '_self',
                    is_visible: true,
                }),
            },
        );

        if (response.ok) {
            const newItem = await response.json();
            emit('items-added', [newItem]);
            customLink.value = { title: '', url: '' };
        }
    } catch (error) {
        console.error('Failed to add custom link:', error);
    } finally {
        adding.value = false;
    }
};

const addSelectedItems = async () => {
    adding.value = true;
    try {
        const itemsToAdd = items.value
            .filter((item) => selectedItems.value.includes(item.id))
            .map((item) => ({
                id: item.id,
                title: item.title || item.name || '',
            }));

        const response = await fetch(
            '/admin/menus/api/' + props.menuId + '/items/bulk-create',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    type: activeTab.value,
                    items: itemsToAdd,
                }),
            },
        );

        if (response.ok) {
            const data = await response.json();
            emit('items-added', data.items);
            selectedItems.value = [];
        }
    } catch (error) {
        console.error('Failed to add items:', error);
    } finally {
        adding.value = false;
    }
};
</script>
