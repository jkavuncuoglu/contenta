<template>
    <div
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="$emit('close')"
    >
        <div
            class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0"
        >
            <!-- Background overlay -->
            <div
                class="bg-opacity-75 dark:bg-opacity-75 fixed inset-0 bg-neutral-500 transition-opacity dark:bg-neutral-900"
                @click="$emit('close')"
            ></div>

            <!-- Center modal -->
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle"
                >&#8203;</span
            >

            <div
                class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle dark:bg-neutral-800"
            >
                <!-- Header -->
                <div
                    class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700"
                >
                    <div class="flex items-center justify-between">
                        <h3
                            class="text-lg font-semibold text-neutral-900 dark:text-white"
                        >
                            Edit Menu Item
                        </h3>
                        <button
                            @click="$emit('close')"
                            class="text-neutral-400 hover:text-neutral-500 dark:hover:text-neutral-300"
                        >
                            <svg
                                class="h-6 w-6"
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
                </div>

                <!-- Body -->
                <form @submit.prevent="save" class="space-y-4 px-6 py-4">
                    <!-- Title -->
                    <div>
                        <label
                            for="title"
                            class="mb-2 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            required
                            class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                        />
                    </div>

                    <!-- URL -->
                    <div>
                        <label
                            for="url"
                            class="mb-2 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            URL
                        </label>
                        <input
                            id="url"
                            v-model="form.url"
                            type="text"
                            class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                            placeholder="/page/about"
                        />
                        <p
                            class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                        >
                            Leave empty for linked items (pages, posts, etc.)
                        </p>
                    </div>

                    <!-- Target -->
                    <div>
                        <label
                            for="target"
                            class="mb-2 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            Open Link In
                        </label>
                        <select
                            id="target"
                            v-model="form.target"
                            class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                        >
                            <option value="_self">Same Tab</option>
                            <option value="_blank">New Tab</option>
                            <option value="_parent">Parent Frame</option>
                            <option value="_top">Full Window</option>
                        </select>
                    </div>

                    <!-- CSS Classes -->
                    <div>
                        <label
                            for="css_classes"
                            class="mb-2 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            CSS Classes
                        </label>
                        <input
                            id="css_classes"
                            v-model="form.css_classes"
                            type="text"
                            class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                            placeholder="btn btn-primary"
                        />
                    </div>

                    <!-- Icon -->
                    <div>
                        <label
                            for="icon"
                            class="mb-2 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            Icon (HTML or Class)
                        </label>
                        <input
                            id="icon"
                            v-model="form.icon"
                            type="text"
                            class="block w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"
                            placeholder="<svg>...</svg> or iconify:icon-name"
                        />
                    </div>

                    <!-- Visibility -->
                    <div class="flex items-center">
                        <input
                            id="is_visible"
                            v-model="form.is_visible"
                            type="checkbox"
                            class="h-4 w-4 rounded border-neutral-300 text-blue-600 focus:ring-blue-500"
                        />
                        <label
                            for="is_visible"
                            class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300"
                        >
                            Visible (show this item in the menu)
                        </label>
                    </div>
                </form>

                <!-- Footer -->
                <div
                    class="flex items-center justify-end gap-3 border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900"
                >
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="rounded-lg border border-neutral-300 bg-white px-4 py-2 font-medium text-neutral-700 transition-colors hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="save"
                        :disabled="saving"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-2 font-semibold text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <svg
                            v-if="saving"
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
                        {{ saving ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
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

interface Props {
    item: MenuItem;
    menuId: number;
}

interface Emits {
    (e: 'save', item: MenuItem): void;
    (e: 'close'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const saving = ref(false);

const form = reactive({
    title: props.item.title,
    url: props.item.url || '',
    target: props.item.target,
    css_classes: props.item.css_classes || '',
    icon: props.item.icon || '',
    is_visible: props.item.is_visible,
});

const save = async () => {
    saving.value = true;
    try {
        const response = await fetch(
            '/admin/menus/api/' + props.menuId + '/items/' + props.item.id,
            {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                },
                body: JSON.stringify(form),
            },
        );

        if (response.ok) {
            const updatedItem = await response.json();
            emit('save', updatedItem);
        } else {
            alert('Failed to save menu item');
        }
    } catch (error) {
        console.error('Failed to save menu item:', error);
        alert('Failed to save menu item');
    } finally {
        saving.value = false;
    }
};
</script>
