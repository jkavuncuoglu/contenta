<template>
    <Head :title="`Edit Menu: ${menu.name}`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2
                        class="text-2xl font-bold text-neutral-900 dark:text-white"
                    >
                        {{ menu.name }}
                    </h2>
                    <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                        Manage menu structure and settings
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="exportMenu"
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
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                            />
                        </svg>
                        Export
                    </button>
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
                        Back
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Main Content Area -->
                <div class="space-y-6 lg:col-span-8">
                    <!-- Menu Items Section -->
                    <div
                        class="rounded-lg border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-800"
                    >
                        <div
                            class="border-b border-neutral-200 p-6 dark:border-neutral-700"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-neutral-900 dark:text-white"
                                    >
                                        Menu Structure
                                    </h3>
                                    <p
                                        class="mt-1 text-sm text-neutral-600 dark:text-neutral-400"
                                    >
                                        Drag and drop to reorder. Click to edit.
                                    </p>
                                </div>
                                <button
                                    @click="
                                        showSourceSelector = !showSourceSelector
                                    "
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
                                    Add Items
                                </button>
                            </div>
                        </div>

                        <!-- Source Selector (collapsible) -->
                        <div
                            v-if="showSourceSelector"
                            class="border-b border-neutral-200 bg-neutral-50 p-6 dark:border-neutral-700 dark:bg-neutral-900"
                        >
                            <MenuSourceSelector
                                :menu-id="menu.id"
                                @items-added="handleItemsAdded"
                                @close="showSourceSelector = false"
                            />
                        </div>

                        <!-- Menu Tree -->
                        <div class="p-6">
                            <MenuTreeView
                                v-model="menuItems"
                                :menu-id="menu.id"
                                @update="saveMenuStructure"
                                @edit="editItem"
                                @delete="deleteItem"
                            />

                            <!-- Empty State -->
                            <div
                                v-if="menuItems.length === 0"
                                class="py-12 text-center"
                            >
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
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                </svg>
                                <h3
                                    class="mt-2 text-sm font-medium text-neutral-900 dark:text-white"
                                >
                                    No menu items
                                </h3>
                                <p
                                    class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                                >
                                    Get started by adding your first menu item
                                </p>
                                <div class="mt-6">
                                    <button
                                        @click="showSourceSelector = true"
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
                                        Add Your First Item
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6 lg:col-span-4">
                    <!-- Menu Settings -->
                    <div
                        class="rounded-lg border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-800"
                    >
                        <div
                            class="border-b border-neutral-200 p-6 dark:border-neutral-700"
                        >
                            <h3
                                class="text-lg font-semibold text-neutral-900 dark:text-white"
                            >
                                Menu Settings
                            </h3>
                        </div>
                        <div class="space-y-4 p-6">
                            <!-- Name -->
                            <div>
                                <label
                                    for="menu-name"
                                    class="mb-2 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Name
                                </label>
                                <input
                                    id="menu-name"
                                    v-model="menuForm.name"
                                    type="text"
                                    @blur="updateMenuSettings"
                                    class="block py-2 px-3 w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                                />
                            </div>

                            <!-- Description -->
                            <div>
                                <label
                                    for="menu-description"
                                    class="mb-2 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Description
                                </label>
                                <textarea
                                    id="menu-description"
                                    v-model="menuForm.description"
                                    rows="3"
                                    @blur="updateMenuSettings"
                                    class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                                />
                            </div>

                            <!-- Location -->
                            <div>
                                <label
                                    for="menu-location"
                                    class="mb-2 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Location
                                </label>
                                <select
                                    id="menu-location"
                                    v-model="menuForm.location"
                                    @change="updateMenuSettings"
                                    class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                                >
                                    <option value="">None</option>
                                    <option
                                        v-for="(label, value) in locations"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Active -->
                            <div class="flex items-center">
                                <input
                                    id="menu-active"
                                    v-model="menuForm.is_active"
                                    type="checkbox"
                                    @change="updateMenuSettings"
                                    class="h-4 w-4 rounded border-neutral-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label
                                    for="menu-active"
                                    class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300"
                                >
                                    Active
                                </label>
                            </div>

                            <!-- Auto-save indicator -->
                            <div
                                v-if="saving"
                                class="flex items-center text-sm text-neutral-600 dark:text-neutral-400"
                            >
                                <svg
                                    class="mr-2 h-4 w-4 animate-spin"
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
                                Saving...
                            </div>
                            <div
                                v-else-if="lastSaved"
                                class="text-sm text-green-600 dark:text-green-400"
                            >
                                ✓ Saved {{ lastSaved }}
                            </div>
                        </div>
                    </div>

                    <!-- Live Preview -->
                    <MenuPreview :items="menuItems" />
                </div>
            </div>
        </div>

        <!-- Edit Item Modal -->
        <MenuItemEditor
            v-if="editingItem"
            :item="editingItem"
            :menu-id="menu.id"
            @save="handleItemSaved"
            @close="editingItem = null"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import MenuItemEditor from '@/components/navigation/MenuItemEditor.vue';
import MenuPreview from '@/components/navigation/MenuPreview.vue';
import MenuSourceSelector from '@/components/navigation/MenuSourceSelector.vue';
import MenuTreeView from '@/components/navigation/MenuTreeView.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

interface MenuItem {
    id: number;
    title: string;
    url: string | null;
    type: string;
    target: string;
    css_classes: string | null;
    icon: string | null;
    is_visible: boolean;
    attributes: Record<string, any> | null;
    metadata: Record<string, any> | null;
    children: MenuItem[];
}

interface Menu {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    location: string | null;
    settings: Record<string, any> | null;
    is_active: boolean;
}

interface Props {
    menu: Menu;
    items: MenuItem[];
    locations: Record<string, string>;
}

const props = defineProps<Props>();

const menuItems = ref<MenuItem[]>(props.items);
const showSourceSelector = ref(false);
const editingItem = ref<MenuItem | null>(null);
const saving = ref(false);
const lastSaved = ref<string>('');

const menuForm = reactive({
    name: props.menu.name,
    description: props.menu.description,
    location: props.menu.location,
    is_active: props.menu.is_active,
});

const updateMenuSettings = async () => {
    saving.value = true;
    try {
        await fetch('/admin/menus/api/' + props.menu.id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || '',
            },
            body: JSON.stringify(menuForm),
        });
        lastSaved.value = new Date().toLocaleTimeString();
    } catch (error) {
        console.error('Failed to update menu settings:', error);
    } finally {
        saving.value = false;
    }
};

const saveMenuStructure = async (items: MenuItem[]) => {
    saving.value = true;
    try {
        const response = await fetch(
            '/admin/menus/api/' + props.menu.id + '/items/reorder',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                },
                body: JSON.stringify({ items }),
            },
        );
        const data = await response.json();
        menuItems.value = data.items;
        lastSaved.value = new Date().toLocaleTimeString();
    } catch (error) {
        console.error('Failed to save menu structure:', error);
    } finally {
        saving.value = false;
    }
};

const editItem = (item: MenuItem) => {
    editingItem.value = item;
};

const handleItemSaved = (updatedItem: MenuItem) => {
    // Update the item in the tree
    const updateInTree = (items: MenuItem[]): MenuItem[] => {
        return items.map((item) => {
            if (item.id === updatedItem.id) {
                return { ...updatedItem, children: item.children };
            }
            if (item.children.length > 0) {
                return { ...item, children: updateInTree(item.children) };
            }
            return item;
        });
    };
    menuItems.value = updateInTree(menuItems.value);
    editingItem.value = null;
    lastSaved.value = new Date().toLocaleTimeString();
};

const deleteItem = async (itemId: number) => {
    if (!confirm('Are you sure you want to delete this menu item?')) return;

    try {
        await fetch('/admin/menus/api/' + props.menu.id + '/items/' + itemId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || '',
            },
        });
        // Remove from tree
        const removeFromTree = (items: MenuItem[]): MenuItem[] => {
            return items.filter((item) => {
                if (item.id === itemId) return false;
                if (item.children.length > 0) {
                    item.children = removeFromTree(item.children);
                }
                return true;
            });
        };
        menuItems.value = removeFromTree(menuItems.value);
    } catch (error) {
        console.error('Failed to delete menu item:', error);
    }
};

const handleItemsAdded = (newItems: MenuItem[]) => {
    menuItems.value = [...menuItems.value, ...newItems];
    showSourceSelector.value = false;
    saveMenuStructure(menuItems.value);
};

const exportMenu = async () => {
    try {
        const response = await fetch(
            '/admin/menus/api/' + props.menu.id + '/export',
        );
        const data = await response.json();
        const blob = new Blob([JSON.stringify(data, null, 2)], {
            type: 'application/json',
        });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `menu-${props.menu.slug}.json`;
        a.click();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Failed to export menu:', error);
    }
};
</script>
