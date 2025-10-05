<template>
  <Head title="Tags" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Tags</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Manage your content tags
          </p>
        </div>
        <Link
          href="/admin/tags/create"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
        >
          <Icon icon="material-symbols-light:add" class="w-4 h-4 mr-2" />
          Add Tag
        </Link>
      </div>

      <!-- Tags List -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div v-if="!tags.length" class="p-12 text-center">
          <Icon icon="material-symbols-light:label" class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tags</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new tag.</p>
          <div class="mt-6">
            <Link
              href="/admin/tags/create"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
            >
              <Icon icon="material-symbols-light:add" class="w-4 h-4 mr-2" />
              Add Tag
            </Link>
          </div>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Slug</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Posts</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="tag in tags" :key="tag.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                  {{ tag.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ tag.slug }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ tag.posts_count || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ formatDate(tag.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <Link :href="`/admin/tags/${tag.id}/edit`" class="text-indigo-600 hover:text-indigo-900">
                      <Icon icon="material-symbols-light:edit" class="w-4 h-4" />
                    </Link>
                    <button @click="deleteTag(tag)" class="text-red-600 hover:text-red-900">
                      <Icon icon="material-symbols-light:delete" class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Icon } from '@iconify/vue';

interface Tag {
  id: number;
  name: string;
  slug: string;
  posts_count?: number;
  created_at: string;
}

interface Props {
  tags: Tag[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tags', href: '/admin/tags' },
];

const deleteTag = (tag: Tag) => {
  if (confirm(`Are you sure you want to delete "${tag.name}"?`)) {
    router.delete(`/admin/tags/${tag.id}`);
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString();
};
</script>
