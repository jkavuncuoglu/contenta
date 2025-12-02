<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{ items: NavItem[]; level?: number }>();

const page = usePage();
const expandedItems = ref<Set<string>>(new Set());

function isStringIcon(icon: any) {
    return typeof icon === 'string' || icon instanceof String;
}

function toggleChildren(itemKey: string) {
    if (expandedItems.value.has(itemKey)) {
        expandedItems.value.delete(itemKey);
    } else {
        expandedItems.value.add(itemKey);
    }
    // Force reactivity for Set
    expandedItems.value = new Set(expandedItems.value);
}

function isItemActive(item: NavItem): boolean {
    if (urlIsActive(item.href, page.url)) return true;
    if (item.children) {
        return item.children.some(isItemActive);
    }
    return false;
}

// Auto-expand parents if any child is active
const autoExpandedItems = computed(() => {
    const expanded = new Set<string>();
    function check(itemsList: NavItem[]) {
        for (const item of itemsList) {
            if (item.children && item.children.some(isItemActive)) {
                expanded.add(item.title);
                check(item.children);
            }
        }
    }
    check(props.items); // Use props.items for correct scope
    return expanded;
});

function isExpanded(item: NavItem): boolean {
    return (
        expandedItems.value.has(item.title) ||
        autoExpandedItems.value.has(item.title)
    );
}
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarMenu>
            <SidebarMenuItem v-for="item in props.items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isItemActive(item)"
                    :tooltip="item.title"
                    class="flex"
                    :class="[
                        'transition-all',
                        isItemActive(item) ? 'bg-charcoal-200 text-white' : '',
                        item.children && isExpanded(item)
                            ? 'rounded-tl-lg rounded-tr-lg'
                            : 'rounded-lg',
                        // Remove bottom rounding if open
                        item.children && isExpanded(item)
                            ? 'rounded-b-none'
                            : 'rounded-tl-lg rounded-tr-lg',
                    ]"
                >
                    <Link
                        :href="item.redirect || item.href"
                        class="flex items-center gap-2"
                    >
                        <template v-if="isStringIcon(item.icon)">
                            <Icon
                                :name="(item.icon as string).split(':')[1]"
                                :icon="item.icon as string"
                                class="h-4 w-4"
                            />
                        </template>
                        <template v-else>
                            <component :is="item.icon" class="h-4 w-4" />
                        </template>
                        <span>{{ item.title }}</span>
                    </Link>
                    <button
                        v-if="item.children"
                        class="absolute -top-1 right-0 inline-flex items-center justify-end p-3"
                        @click="toggleChildren(item.title)"
                    >
                        <Icon
                            v-if="isExpanded(item)"
                            name="chevron-right"
                            icon="material-symbols-light:chevron-right"
                            class="h-4 w-4 rotate-90 text-white"
                        />
                        <Icon
                            v-else
                            name="chevron-right"
                            icon="material-symbols-light:chevron-right"
                            class="h-4 w-4 text-white"
                        />
                    </button>
                </SidebarMenuButton>
                <template v-if="item.children && isExpanded(item)">
                    <div class="rounded-lg rounded-t-none bg-neutral-100 py-2">
                        <NavMain
                            :items="item.children"
                            :level="(props.level ?? 0) + 1"
                        />
                    </div>
                </template>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
