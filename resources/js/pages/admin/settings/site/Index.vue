<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Settings',
    },
    {
        title: 'Site',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Site Settings" />

        <div class="space-y-6">
            <HeadingSmall
                title="Site settings"
                description="General Site settings"
            />
        </div>
    </AppLayout>
</template>
