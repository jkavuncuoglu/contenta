<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Sidebar -->
        <AdminSidebar :is-open="sidebarOpen" />

        <!-- Main content -->
        <div
            class="transition-all duration-300 ease-in-out"
            :class="{ 'ml-64': sidebarOpen, 'ml-0': !sidebarOpen }"
        >
            <!-- Top bar -->
            <header class="bg-white shadow-sm dark:bg-gray-800">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center">
                        <button
                            @click="sidebarOpen = !sidebarOpen"
                            class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:ring-2 focus:ring-blue-500 focus:outline-none focus:ring-inset dark:hover:bg-gray-700"
                        >
                            <Bars3Icon class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button
                            class="p-2 text-gray-400 hover:text-gray-500 dark:text-gray-300 dark:hover:text-gray-200"
                        >
                            <BellIcon class="h-6 w-6" />
                        </button>

                        <!-- User menu -->
                        <UserMenu v-if="user" />
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="p-6">
                <RouterView />
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import AdminSidebar from '@/components/admin/AdminSidebar.vue';
import UserMenu from '@/components/UserMenu.vue';
import { useAuthStore } from '@/stores/auth';
import { Bars3Icon, BellIcon } from '@heroicons/vue/24/outline';
import { computed, ref } from 'vue';

const authStore = useAuthStore();

const sidebarOpen = ref(true);

const user = computed(() => authStore.user);
</script>
