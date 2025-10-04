<template>
  <Head title="Comments" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h1 class="text-xl font-semibold">Comments</h1>
      <p class="mt-2 text-sm text-gray-600">Manage comments on posts and pages.</p>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Content', href: '/admin/content' },
  { title: 'Comments' },
];
</script>

