<template>
  <Head :title="`Post #${id}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="prose">
      <h1>Post Details</h1>
      <p>Showing details for post with id: {{ id }}</p>
      <pre class="mt-4 bg-gray-100 p-4 rounded">{{ JSON.stringify($props, null, 2) }}</pre>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

interface Props {
  id: number | string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Posts', href: '/admin/posts' },
  { title: `Post #${(typeof ({} as Props).id)}` },
];
</script>

