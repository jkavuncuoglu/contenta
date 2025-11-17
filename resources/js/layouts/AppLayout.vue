<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import {
    SidebarInset,
    SidebarProvider,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

defineProps<Props>();

const page = usePage();
const isProfileMenuOpen = ref(false);

const user = computed(() => page.props.auth?.user);

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <SidebarProvider>
        <AppSidebar />
        <SidebarInset>
            <!-- Header with breadcrumbs -->
            <header class="flex h-16 shrink-0 items-center gap-2 border-b px-4">
                <SidebarTrigger class="-ml-1" />
                <nav
                    class="flex items-center space-x-1 text-sm text-muted-foreground"
                    v-if="breadcrumbs?.length"
                >
                    <template
                        v-for="(breadcrumb, index) in breadcrumbs"
                        :key="index"
                    >
                        <Link
                            v-if="
                                breadcrumb.href &&
                                index < breadcrumbs.length - 1
                            "
                            :href="breadcrumb.href"
                            class="hover:text-foreground"
                        >
                            {{ breadcrumb.title }}
                        </Link>
                        <span v-else class="font-medium text-foreground">{{
                            breadcrumb.title
                        }}</span>
                        <span v-if="index < breadcrumbs.length - 1" class="mx-2"
                            >/</span
                        >
                    </template>
                </nav>
            </header>

            <!-- Main content -->
            <main class="flex flex-1 flex-col gap-4 overscroll-y-auto p-4">
                <slot />
            </main>
        </SidebarInset>
    </SidebarProvider>
</template>

<style scoped>
/* Add any custom styles here */
</style>
