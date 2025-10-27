<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
    >
        <AppLogoIcon class="size-3 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm py-5">
        <span class="mb-0.5 truncate leading-tight font-semibold"
            >Contenta</span
        >
        <span class="text-xs text-neutral-300">
            Content Creation Simplified
        </span>
    </div>
</template>
