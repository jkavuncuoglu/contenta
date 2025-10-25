<template>
  <Head title="Analytics" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Analytics</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            View your site analytics and statistics
          </p>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <Icon icon="lucide:eye" class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Total Views
                  </dt>
                  <dd class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ stats.totalViews || 0 }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <Icon icon="lucide:users" class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Unique Visitors
                  </dt>
                  <dd class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ stats.uniqueVisitors || 0 }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <Icon icon="lucide:file-text" class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Total Posts
                  </dt>
                  <dd class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ stats.totalPosts || 0 }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <Icon icon="lucide:message-circle" class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Total Comments
                  </dt>
                  <dd class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ stats.totalComments || 0 }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Placeholder -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Visitors Over Time</h3>
          <div class="h-64 flex items-center justify-center text-gray-500 dark:text-gray-400">
            <p>Chart placeholder - integrate with your analytics service</p>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Top Pages</h3>
          <div class="space-y-3">
            <div v-for="page in topPages" :key="page.path" class="flex justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-300">{{ page.path }}</span>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ page.views }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Icon } from '@iconify/vue';

interface AnalyticsStats {
  totalViews: number;
  uniqueVisitors: number;
  totalPosts: number;
  totalComments: number;
}

interface TopPage {
  path: string;
  views: number;
}

interface Props {
  stats: AnalyticsStats;
  topPages: TopPage[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Analytics', href: '/admin/analytics' },
];
</script>
