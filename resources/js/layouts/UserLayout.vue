<template>
    <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
        <!-- Header -->
        <Header />

        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
                <!-- Sidebar -->
                <UserSidebar />

                <!-- Main content -->
                <div class="space-y-6 sm:px-6 lg:col-span-9 lg:px-0">
                    <RouterView />
                </div>
            </div>
        </div>

        <!-- Footer -->
        <Footer />
    </div>
</template>

<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';
import UserSidebar from '@/components/UserSidebar.vue';
</script>
