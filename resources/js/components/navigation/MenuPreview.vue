<template>
    <div
        class="rounded-lg border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-800"
    >
        <div class="border-b border-neutral-200 p-6 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
                    Live Preview
                </h3>
                <div class="flex items-center gap-2">
                    <button
                        @click="viewMode = 'desktop'"
                        :class="{
                            'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300':
                                viewMode === 'desktop',
                            'bg-neutral-100 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-400':
                                viewMode !== 'desktop',
                        }"
                        class="rounded-md px-3 py-1 text-sm font-medium transition-colors"
                        title="Desktop View"
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
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                            />
                        </svg>
                    </button>
                    <button
                        @click="viewMode = 'mobile'"
                        :class="{
                            'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300':
                                viewMode === 'mobile',
                            'bg-neutral-100 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-400':
                                viewMode !== 'mobile',
                        }"
                        class="rounded-md px-3 py-1 text-sm font-medium transition-colors"
                        title="Mobile View"
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
                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Desktop Preview -->
            <div
                v-if="viewMode === 'desktop'"
                class="rounded-lg border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-900"
            >
                <nav class="preview-nav">
                    <ul class="flex flex-wrap gap-1">
                        <MenuPreviewItem
                            v-for="item in visibleItems"
                            :key="item.id"
                            :item="item"
                            :level="0"
                            mode="desktop"
                        />
                    </ul>
                </nav>

                <!-- Empty State -->
                <div v-if="visibleItems.length === 0" class="py-8 text-center">
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        No visible menu items to preview
                    </p>
                </div>
            </div>

            <!-- Mobile Preview -->
            <div
                v-if="viewMode === 'mobile'"
                class="mx-auto rounded-lg border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-900"
                style="max-width: 375px"
            >
                <nav class="preview-nav-mobile">
                    <ul class="space-y-1">
                        <MenuPreviewItem
                            v-for="item in visibleItems"
                            :key="item.id"
                            :item="item"
                            :level="0"
                            mode="mobile"
                        />
                    </ul>
                </nav>

                <!-- Empty State -->
                <div v-if="visibleItems.length === 0" class="py-8 text-center">
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        No visible menu items to preview
                    </p>
                </div>
            </div>

            <!-- Preview Note -->
            <div
                class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-900/20"
            >
                <p class="text-xs text-blue-800 dark:text-blue-300">
                    <svg
                        class="mr-1 inline h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    This is a simplified preview. Actual styling may vary based
                    on your theme.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import MenuPreviewItem from './MenuPreviewItem.vue';

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
    children?: MenuItem[];
}

interface Props {
    items: MenuItem[];
}

const props = defineProps<Props>();

const viewMode = ref<'desktop' | 'mobile'>('desktop');

const visibleItems = computed(() => {
    return props.items.filter((item) => item.is_visible);
});
</script>
