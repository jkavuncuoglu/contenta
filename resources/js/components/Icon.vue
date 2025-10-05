<script setup lang="ts">
import { cn } from '@/lib/utils';
import { Icon } from '@iconify/vue';
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

const icon = computed(() => `mdi:${props.name}`);
</script>

<template>
    <Icon
        v-if="icon"
        :icon="icon"
        :class="className"
        :width="size"
        :height="size"
        :color="color"
    />
</template>
