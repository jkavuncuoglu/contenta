<template>
    <Head title="Analytics" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <h1 class="text-xl font-semibold">Analytics Dashboard</h1>
            <p class="mt-2 text-sm text-neutral-600">
                Overview and charts will appear here.
            </p>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { label: 'Analytics', href: '/admin/analytics' },
];
</script>
