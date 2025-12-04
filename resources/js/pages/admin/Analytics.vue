<template>
    <Head title="Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white"
                    >
                        Analytics
                    </h1>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        View your site analytics and statistics
                    </p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div
                    class="overflow-hidden rounded-lg bg-white shadow dark:bg-neutral-800"
                >
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <Icon
                                    icon="lucide:eye"
                                    class="h-6 w-6 text-neutral-400"
                                />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="truncate text-sm font-medium text-neutral-500 dark:text-neutral-400"
                                    >
                                        Total Views
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-neutral-900 dark:text-white"
                                    >
                                        {{ stats.totalViews || 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-lg bg-white shadow dark:bg-neutral-800"
                >
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <Icon
                                    icon="lucide:users"
                                    class="h-6 w-6 text-neutral-400"
                                />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="truncate text-sm font-medium text-neutral-500 dark:text-neutral-400"
                                    >
                                        Unique Visitors
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-neutral-900 dark:text-white"
                                    >
                                        {{ stats.uniqueVisitors || 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-lg bg-white shadow dark:bg-neutral-800"
                >
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <Icon
                                    icon="lucide:file-text"
                                    class="h-6 w-6 text-neutral-400"
                                />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="truncate text-sm font-medium text-neutral-500 dark:text-neutral-400"
                                    >
                                        Total Posts
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-neutral-900 dark:text-white"
                                    >
                                        {{ stats.totalPosts || 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-lg bg-white shadow dark:bg-neutral-800"
                >
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <Icon
                                    icon="lucide:message-circle"
                                    class="h-6 w-6 text-neutral-400"
                                />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="truncate text-sm font-medium text-neutral-500 dark:text-neutral-400"
                                    >
                                        Total Comments
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-neutral-900 dark:text-white"
                                    >
                                        {{ stats.totalComments || 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Placeholder -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                    <h3
                        class="mb-4 text-lg font-medium text-neutral-900 dark:text-white"
                    >
                        Visitors Over Time
                    </h3>
                    <div
                        class="flex h-64 items-center justify-center text-neutral-500 dark:text-neutral-400"
                    >
                        <p>
                            Chart placeholder - integrate with your analytics
                            service
                        </p>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                    <h3
                        class="mb-4 text-lg font-medium text-neutral-900 dark:text-white"
                    >
                        Top Pages
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="page in topPages"
                            :key="page.path"
                            class="flex justify-between"
                        >
                            <span
                                class="text-sm text-neutral-600 dark:text-neutral-300"
                                >{{ page.path }}</span
                            >
                            <span
                                class="text-sm font-medium text-neutral-900 dark:text-white"
                                >{{ page.views }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Icon } from '@iconify/vue';
import { Head } from '@inertiajs/vue3';

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
