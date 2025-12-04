<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="mb-4">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">
                    Admin Dashboard
                </h1>
                <p class="text-neutral-600 dark:text-neutral-400">
                    Manage your application content and settings
                </p>
            </div>

            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                    <div
                        class="absolute inset-0 flex items-center justify-center"
                    >
                        <div class="text-center">
                            <h3
                                class="font-semibold text-neutral-900 dark:text-white"
                            >
                                Posts
                            </h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                Manage blog posts
                            </p>
                        </div>
                    </div>
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                    <div
                        class="absolute inset-0 flex items-center justify-center"
                    >
                        <div class="text-center">
                            <h3
                                class="font-semibold text-neutral-900 dark:text-white"
                            >
                                Users
                            </h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                User management
                            </p>
                        </div>
                    </div>
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                    <div
                        class="absolute inset-0 flex items-center justify-center"
                    >
                        <div class="text-center">
                            <h3
                                class="font-semibold text-neutral-900 dark:text-white"
                            >
                                Settings
                            </h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                System configuration
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <h3 class="font-semibold text-neutral-900 dark:text-white">
                            Analytics Overview
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">
                            Site statistics and reports
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
