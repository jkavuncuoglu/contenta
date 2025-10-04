<template>
  <div class="space-y-6">
    <!-- Page header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Categories</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Organize your content with categories
        </p>
      </div>
      <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <PlusIcon class="w-4 h-4 mr-2" />
        New Category
      </button>
    </div>

    <!-- Placeholder content -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
      <div class="text-center py-12">
        <FolderIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Categories</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Category management interface will be implemented here.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Folder as FolderIcon, Plus as PlusIcon } from 'lucide-vue-next';
</script>
