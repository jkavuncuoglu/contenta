<template>
    <!-- Desktop Mode -->
    <li v-if="mode === 'desktop'" class="group relative">
        <a
            :href="item.url || '#'"
            :target="item.target"
            :class="item.css_classes"
            class="flex items-center gap-2 rounded-md px-4 py-2 text-gray-700 transition-colors hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
        >
            <!-- Icon -->
            <span
                v-if="item.icon"
                v-html="item.icon"
                class="h-4 w-4 flex-shrink-0"
            ></span>

            <!-- Title -->
            <span>{{ item.title }}</span>

            <!-- Dropdown indicator for children -->
            <svg
                v-if="hasVisibleChildren"
                class="ml-1 h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                />
            </svg>
        </a>

        <!-- Desktop Dropdown for Children -->
        <ul
            v-if="hasVisibleChildren"
            class="invisible absolute top-full left-0 z-10 mt-1 min-w-[200px] rounded-lg border border-gray-200 bg-white opacity-0 shadow-lg transition-all group-hover:visible group-hover:opacity-100 dark:border-gray-700 dark:bg-gray-800"
        >
            <MenuPreviewItem
                v-for="child in visibleChildren"
                :key="child.id"
                :item="child"
                :level="level + 1"
                mode="desktop-dropdown"
            />
        </ul>
    </li>

    <!-- Desktop Dropdown Mode -->
    <li v-else-if="mode === 'desktop-dropdown'" class="group/submenu relative">
        <a
            :href="item.url || '#'"
            :target="item.target"
            :class="item.css_classes"
            class="flex items-center justify-between gap-2 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
        >
            <span class="flex items-center gap-2">
                <span
                    v-if="item.icon"
                    v-html="item.icon"
                    class="h-4 w-4 flex-shrink-0"
                ></span>
                <span>{{ item.title }}</span>
            </span>

            <svg
                v-if="hasVisibleChildren"
                class="h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5l7 7-7 7"
                />
            </svg>
        </a>

        <!-- Nested Dropdown -->
        <ul
            v-if="hasVisibleChildren"
            class="invisible absolute top-0 left-full ml-1 min-w-[200px] rounded-lg border border-gray-200 bg-white opacity-0 shadow-lg transition-all group-hover/submenu:visible group-hover/submenu:opacity-100 dark:border-gray-700 dark:bg-gray-800"
        >
            <MenuPreviewItem
                v-for="child in visibleChildren"
                :key="child.id"
                :item="child"
                :level="level + 1"
                mode="desktop-dropdown"
            />
        </ul>
    </li>

    <!-- Mobile Mode -->
    <li v-else class="mobile-item">
        <button
            v-if="hasVisibleChildren"
            @click="mobileExpanded = !mobileExpanded"
            class="flex w-full items-center justify-between gap-2 rounded-md px-3 py-2 text-left text-gray-700 transition-colors hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
            :style="{ paddingLeft: `${level * 16 + 12}px` }"
        >
            <span class="flex items-center gap-2">
                <span
                    v-if="item.icon"
                    v-html="item.icon"
                    class="h-4 w-4 flex-shrink-0"
                ></span>
                <span>{{ item.title }}</span>
            </span>

            <svg
                class="h-4 w-4 transition-transform"
                :class="{ 'rotate-180': mobileExpanded }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                />
            </svg>
        </button>

        <a
            v-else
            :href="item.url || '#'"
            :target="item.target"
            :class="item.css_classes"
            class="flex items-center gap-2 rounded-md px-3 py-2 text-gray-700 transition-colors hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
            :style="{ paddingLeft: `${level * 16 + 12}px` }"
        >
            <span
                v-if="item.icon"
                v-html="item.icon"
                class="h-4 w-4 flex-shrink-0"
            ></span>
            <span>{{ item.title }}</span>
        </a>

        <!-- Mobile Children (Collapsible) -->
        <ul v-if="hasVisibleChildren && mobileExpanded" class="mt-1 space-y-1">
            <MenuPreviewItem
                v-for="child in visibleChildren"
                :key="child.id"
                :item="child"
                :level="level + 1"
                mode="mobile"
            />
        </ul>
    </li>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

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
    item: MenuItem;
    level: number;
    mode: 'desktop' | 'mobile' | 'desktop-dropdown';
}

const props = defineProps<Props>();

const mobileExpanded = ref(false);

const visibleChildren = computed(() => {
    return (props.item.children || []).filter((child) => child.is_visible);
});

const hasVisibleChildren = computed(() => {
    return visibleChildren.value.length > 0;
});
</script>

<style scoped>
.rotate-180 {
    transform: rotate(180deg);
}
</style>
