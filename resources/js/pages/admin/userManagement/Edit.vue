<template>
    <Head :title="`Edit User #${id}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <h1 class="text-xl font-semibold">Edit User #{{ id }}</h1>
            <pre class="mt-4 rounded bg-gray-100 p-4">{{
                JSON.stringify($props, null, 2)
            }}</pre>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Props {
    id: number | string;
}

const props = defineProps<Props>();

const id = props.id ?? '';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: '/admin/users' },
    { title: `Edit User #${id}` },
];
</script>
