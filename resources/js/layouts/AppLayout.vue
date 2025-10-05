<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import type { BreadcrumbItemType } from '@/types';
import AppSidebar from '@/components/AppSidebar.vue';
import { SidebarProvider, SidebarInset, SidebarTrigger } from '@/components/ui/sidebar';

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
        <nav class="flex items-center space-x-1 text-sm text-muted-foreground" v-if="breadcrumbs?.length">
          <template v-for="(breadcrumb, index) in breadcrumbs" :key="index">
            <Link
              v-if="breadcrumb.href && index < breadcrumbs.length - 1"
              :href="breadcrumb.href"
              class="hover:text-foreground"
            >
              {{ breadcrumb.title }}
            </Link>
            <span v-else class="font-medium text-foreground">{{ breadcrumb.title }}</span>
            <span v-if="index < breadcrumbs.length - 1" class="mx-2">/</span>
          </template>
        </nav>

        <!-- User menu -->
        <div class="ml-auto flex items-center">
          <div class="relative">
            <button
              @click="isProfileMenuOpen = !isProfileMenuOpen"
              class="flex items-center max-w-xs rounded-full bg-background text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ring"
            >
              <span class="sr-only">Open user menu</span>
              <div class="h-8 w-8 rounded-full bg-muted flex items-center justify-center">
                <span class="text-foreground font-medium">
                  {{ user?.first_name?.charAt(0) || 'U' }}
                </span>
              </div>
            </button>
            <transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-if="isProfileMenuOpen"
                class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-popover border ring-1 ring-border focus:outline-none z-50"
                role="menu"
                tabindex="-1"
              >
                <div class="py-1" role="none">
                  <Link
                    href="/settings/profile"
                    class="block px-4 py-2 text-sm text-popover-foreground hover:bg-accent"
                    role="menuitem"
                  >
                    Your Profile
                  </Link>
                  <button
                    @click="logout"
                    class="w-full text-left px-4 py-2 text-sm text-popover-foreground hover:bg-accent"
                    role="menuitem"
                  >
                    Sign out
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </header>

      <!-- Main content -->
      <main class="flex flex-1 flex-col gap-4 p-4">
        <slot />
      </main>
    </SidebarInset>
  </SidebarProvider>
</template>

<style scoped>
/* Add any custom styles here */
</style>
