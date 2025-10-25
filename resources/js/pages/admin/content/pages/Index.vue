<template>
  <Head title="Pages" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Page header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Pages</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Manage your site pages
          </p>
        </div>
        <Link
          href="/admin/pages/create"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <Icon name="plus" class="w-4 h-4 mr-2" />
          New Page
        </Link>
      </div>

      <!-- Pages table -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div v-if="pages.length === 0" class="p-6 text-center">
          <Icon name="document-text" class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No pages</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Get started by creating your first page.
          </p>
          <div class="mt-6">
            <Link
              href="/admin/pages/create"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <Icon name="plus" class="w-4 h-4 mr-2" />
              New Page
            </Link>
          </div>
        </div>

        <div v-else>
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Title
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Date
                </th>
                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">Actions</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="page in pages.value.filter(Boolean)" :key="page.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ page.title }}
                      </p>
                      <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                        {{ page.slug }}
                      </p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusClass(page.published)">
                    {{ page.published ? 'Published' : 'Draft' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ formatDate(page.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center space-x-2">
                    <Link
                      :href="`/admin/pages/${page.id}/edit`"
                      class="text-blue-600 dark:text-blue-400 hover:text-blue-500"
                    >
                      Edit
                    </Link>
                    <button
                      @click="deletePage(page)"
                      class="text-red-600 dark:text-red-400 hover:text-red-500"
                    >
                      Delete
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
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import Icon from '@/components/Icon.vue';
import { computed } from 'vue';

interface Page {
  id: number;
  title: string;
  slug: string;
  published: boolean;
  created_at: string;
}

interface Props {
  pages?: Page[];
}

const props = defineProps<Props>();
const page = usePage();
const pages = computed<Page[]>(() => {
  const p = (page.props as any)?.value ?? (page as any)?.props ?? {};
  const fromPage = props.pages ?? p.pages ?? p.data?.pages ?? p.pageList ?? [];
  if (Array.isArray(fromPage)) return fromPage;
  if (fromPage && Array.isArray(fromPage.data)) return fromPage.data;
  return [];
});

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Pages',
    href: '/admin/pages',
  },
];

const deletePage = (page: Page) => {
  if (confirm(`Are you sure you want to delete "${page.title}"?`)) {
    router.delete(`/admin/pages/${page.id}`);
  }
};

const getStatusClass = (published: boolean) => {
  return published
    ? 'bg-green-100 text-green-800'
    : 'bg-yellow-100 text-yellow-800';
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString();
};
</script>
