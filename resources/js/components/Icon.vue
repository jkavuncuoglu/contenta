<script setup lang="ts">
import { cn } from '@/lib/utils';
import * as icons from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    name: string;
    class?: string;
    size?: number | string;
    color?: string;
    strokeWidth?: number | string;
}

const props = withDefaults(defineProps<Props>(), {
    class: '',
    size: 16,
    strokeWidth: 2,
});

const className = computed(() => cn('h-4 w-4', props.class));

function toPascalCase(input: string) {
    if (!input) return '';
    // Normalize separators to spaces
    let s = input.replace(/[-_]+/g, ' ');
    // Insert spaces between camelCase boundaries
    s = s.replace(/([a-z0-9])([A-Z])/g, '$1 $2');
    // Split on spaces and capitalize
    return s
        .split(/\s+/)
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join('');
}

const icon = computed(() => {
    const iconName = toPascalCase(props.name);
    // Try direct lookup
    const found = (icons as Record<string, any>)[iconName];
    if (found) return found;
    // fallback: try common aliases (e.g., X -> X)
    return (icons as Record<string, any>)[iconName] || undefined;
});
</script>

<template>
    <component
        v-if="icon"
        :is="icon"
        :class="className"
        :size="size"
        :stroke-width="strokeWidth"
        :color="color"
    />
</template>
