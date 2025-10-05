<template>
  <div class="space-y-6">
    <!-- Page header -->
    <div>
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Profile Settings</h1>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Manage your account settings and preferences
      </p>
    </div>

    <!-- User info -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
      <div class="flex items-center space-x-6">
        <img class="h-16 w-16 rounded-full" :src="user?.avatar || '/default-avatar.png'" :alt="user?.name" />
        <div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ user?.name }}</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ user?.email }}</p>
          <div class="mt-1">
            <span
              v-for="role in user?.roles"
              :key="role"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 mr-2"
            >
              {{ role }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Placeholder content -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
      <div class="text-center py-12">
        <Icon icon="material-symbols-light:person" class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Profile Settings</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Profile management interface will be implemented here.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { Icon } from '@iconify/vue';

const authStore = useAuthStore();
const user = computed(() => authStore.user);
</script>
