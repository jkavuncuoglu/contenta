<template>
    <Head title="Content" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <h1 class="text-xl font-semibold">Content</h1>
            <p class="mt-2 text-sm text-neutral-600">
                Manage pages, media, and comments.
            </p>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Content', href: '/admin/content' },
];
</script>
