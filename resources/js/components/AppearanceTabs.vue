<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Icon } from '@iconify/vue';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    {
        value: 'light',
        icon: 'material-symbols-light:light_mode',
        label: 'Light',
    },
    { value: 'dark', icon: 'material-symbols-light:dark_mode', label: 'Dark' },
    {
        value: 'system',
        icon: 'material-symbols-light:monitor',
        label: 'System',
    },
] as const;
</script>

<template>
    <div
        class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800"
    >
        <button
            v-for="{ value, icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                appearance === value
                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
            ]"
        >
            <Icon :icon="icon" class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>
