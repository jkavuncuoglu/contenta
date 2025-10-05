<template>
  <Head title="Categories" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Categories</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Manage your content categories and their hierarchy
          </p>
        </div>
        <Link
          href="/admin/categories/create"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <PlusIcon class="w-4 h-4 mr-2" />
          Add Category
        </Link>
      </div>

      <!-- Categories List -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Categories ({{ categories.length }})
          </h3>
        </div>

        <div v-if="!categories.length" class="p-12 text-center">
          <FolderIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No categories</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new category.</p>
          <div class="mt-6">
            <Link
              href="/admin/categories/create"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
            >
              <PlusIcon class="w-4 h-4 mr-2" />
              Add Category
            </Link>
          </div>
        </div>

        <div v-else>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Name
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Slug
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Parent
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Posts
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Created
                  </th>
                  <th class="relative px-6 py-3">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                          {{ category.name }}
                        </div>
                        <div v-if="category.description" class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                          {{ category.description }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ category.slug }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ category.parent?.name || '—' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ category.posts_count || 0 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ formatDate(category.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end space-x-2">
                      <Link
                        :href="`/admin/categories/${category.id}/edit`"
                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                      >
                        <PencilIcon class="w-4 h-4" />
                      </Link>
                      <button
                        @click="deleteCategory(category)"
                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                      >
                        <TrashIcon class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import {
  Plus as PlusIcon,
  Pencil as PencilIcon,
  Trash as TrashIcon,
  Folder as FolderIcon,
} from 'lucide-vue-next';
import { defineProps } from 'vue';

interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  posts_count?: number;
  created_at: string;
  parent?: {
    name: string;
  };
}

interface Props {
  categories: Category[];
}

const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
    required: false
  },
  meta: {
    type: Object,
    default: () => ({ current_page: 1, last_page: 1, per_page: 15, total: 0 }),
    required: false
  }
});

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Categories',
    href: '/admin/categories',
  },
];

const deleteCategory = (category: Category) => {
  if (confirm(`Are you sure you want to delete "${category.name}"?`)) {
    router.delete(`/admin/categories/${category.id}`);
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString();
};
</script>
