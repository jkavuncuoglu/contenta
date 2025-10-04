<template>
  <Head :title="`User #${id}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h1 class="text-xl font-semibold">User #{{ id }}</h1>
      <pre class="mt-4 bg-gray-100 p-4 rounded">{{ JSON.stringify($props, null, 2) }}</pre>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

interface Props { id: number | string }

defineProps<Props>();

const id = (props as any)?.id ?? '';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Users', href: '/admin/users' },
  { title: `User #${id}` },
];
</script>

