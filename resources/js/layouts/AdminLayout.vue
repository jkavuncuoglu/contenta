<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import {
    SidebarInset,
    SidebarProvider,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import { Icon } from '@iconify/vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <SidebarProvider>
        <AppSidebar />
        <SidebarInset>
            <!-- Header -->
            <header class="flex h-16 shrink-0 items-center gap-2 border-b px-4">
                <SidebarTrigger class="-ml-1" />
                <div class="flex-1"></div>

                <!-- Right side actions -->
                <div class="flex items-center gap-2">
                    <button
                        class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500"
                    >
                        <Icon
                            icon="material-symbols-light:notifications"
                            class="h-5 w-5"
                        />
                    </button>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-auto">
                <slot />
            </main>
        </SidebarInset>
    </SidebarProvider>
</template>
