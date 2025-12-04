<script setup lang="ts">
import PageLayout from '@/layouts/PageLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <PageLayout>
        <Head title="Welcome" />

        <div
            class="bg-gradient-to-b from-blue-50 to-white dark:from-neutral-900 dark:to-neutral-800"
        >
            <!-- Hero Section -->
            <div class="container mx-auto px-4 py-20 lg:py-32">
                <div class="mx-auto max-w-4xl text-center">
                    <h1
                        class="mb-6 text-5xl font-bold text-neutral-900 lg:text-7xl dark:text-white"
                    >
                        Welcome to
                        <span class="text-blue-600 dark:text-blue-400"
                            >Contenta</span
                        >
                    </h1>
                    <p
                        class="mx-auto mb-8 max-w-2xl text-xl text-neutral-600 dark:text-neutral-300"
                    >
                        Professional content management made simple. Create,
                        manage, and publish your content with ease.
                    </p>
                    <div
                        class="flex flex-wrap items-center justify-center gap-4"
                    >
                        <a
                            href="/blog"
                            class="rounded-lg bg-blue-600 px-8 py-3 font-semibold text-white transition-colors hover:bg-blue-700"
                        >
                            Explore Blog
                        </a>
                        <a
                            v-if="!$page.props.auth.user"
                            href="/register"
                            class="rounded-lg border-2 border-blue-600 px-8 py-3 font-semibold text-blue-600 transition-colors hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20"
                        >
                            Get Started
                        </a>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="container mx-auto px-4 py-16 pb-24">
                <div
                    class="mx-auto grid max-w-6xl grid-cols-1 gap-8 md:grid-cols-3"
                >
                    <div
                        class="rounded-xl bg-white p-8 shadow-sm dark:bg-neutral-800"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30"
                        >
                            <svg
                                class="h-6 w-6 text-blue-600 dark:text-blue-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                ></path>
                            </svg>
                        </div>
                        <h3
                            class="mb-2 text-xl font-semibold text-neutral-900 dark:text-white"
                        >
                            Easy Content Management
                        </h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Create and manage your content with an intuitive
                            interface designed for efficiency.
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-white p-8 shadow-sm dark:bg-neutral-800"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30"
                        >
                            <svg
                                class="h-6 w-6 text-purple-600 dark:text-purple-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"
                                ></path>
                            </svg>
                        </div>
                        <h3
                            class="mb-2 text-xl font-semibold text-neutral-900 dark:text-white"
                        >
                            Customizable Design
                        </h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Tailor your site's appearance with our powerful
                            theme customization tools.
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-white p-8 shadow-sm dark:bg-neutral-800"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30"
                        >
                            <svg
                                class="h-6 w-6 text-green-600 dark:text-green-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"
                                ></path>
                            </svg>
                        </div>
                        <h3
                            class="mb-2 text-xl font-semibold text-neutral-900 dark:text-white"
                        >
                            Lightning Fast
                        </h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Built with modern technologies for optimal
                            performance and user experience.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </PageLayout>
</template>
