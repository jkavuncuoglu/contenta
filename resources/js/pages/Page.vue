<script setup lang="ts">
import PageLayout from '@/layouts/PageLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { Component } from 'vue';

interface BlockConfig {
    id: string;
    type: string;
    config: Record<string, any>;
}

interface PageData {
    id: number;
    title: string;
    slug: string;
    data: {
        layout?: string;
        sections?: BlockConfig[];
    };
    // Server-rendered HTML from publish action
    content_html?: string | null;
    // Original markdown source
    content_markdown?: string | null;
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
}

interface Props {
    page: PageData | null;
    message?: string;
}

const props = defineProps<Props>();

// Helper to detect if legacy pagebuilder sections exist
const hasSections = (p: PageData | null) => {
    return p?.data && Array.isArray(p.data.sections) && p.data.sections.length > 0;
};

// Helper to get server-rendered HTML (preferred)
const getContentHtml = (p: PageData | null) => {
    if (!p) return null;
    // Check root level content_html (from published pages)
    // Return undefined for null/undefined, but allow empty string
    if (p.content_html === null || p.content_html === undefined) {
        return null;
    }
    return p.content_html;
};

// Check if page has published HTML content (even if empty)
const hasPublishedContent = (p: PageData | null) => {
    if (!p) return false;
    return p.content_html !== null && p.content_html !== undefined;
};
</script>

<template>
    <PageLayout>
        <Head v-if="page" :title="page.meta_title || page.title">
            <meta
                v-if="page.meta_description"
                name="description"
                :content="page.meta_description"
            />
            <meta
                v-if="page.meta_keywords"
                name="keywords"
                :content="page.meta_keywords"
            />
        </Head>

        <div class="min-h-screen">
            <!-- Page not found -->
            <div v-if="!page" class="flex items-center justify-center px-4 py-16">
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-neutral-900 dark:text-white mb-4">Page Not Found</h1>
                    <p class="text-neutral-600 dark:text-neutral-400 mb-8">{{ props.message || 'The page you are looking for does not exist.' }}</p>
                    <a href="/" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Go Home
                    </a>
                </div>
            </div>

            <!-- Page content -->
            <template v-else>
                <!-- Prefer server-rendered HTML (new Markdown editor) -->
                <template v-if="hasPublishedContent(page)">
                    <div class="markdown-content" v-html="getContentHtml(page)"></div>
                </template>

                <!-- If no server HTML but page has legacy sections, render a safe fallback -->
                <template v-else-if="hasSections(page)">
                    <template v-for="section in page.data.sections" :key="section.id">
                        <div v-if="section.config?.html" v-html="section.config.html"></div>

                        <!-- If section provides raw content, show it -->
                        <div v-else-if="section.config && Object.keys(section.config).length">
                            <div class="prose max-w-full px-4 py-6">
                                <h3 class="text-2xl font-semibold mb-2">{{ section.config.title || section.type }}</h3>
                                <div v-if="section.config.content" v-html="section.config.content"></div>
                                <pre v-else class="whitespace-pre-wrap">{{ JSON.stringify(section.config, null, 2) }}</pre>
                            </div>
                        </div>

                        <!-- Fallback for unknown/empty sections -->
                        <div v-else class="border-l-4 border-yellow-400 bg-yellow-50 px-4 py-8 dark:bg-yellow-900/20">
                            <p class="text-yellow-800 dark:text-yellow-200">
                                Legacy block: <strong>{{ section.type }}</strong> (no client renderer available)
                            </p>
                        </div>
                    </template>
                </template>

                <!-- Page not published yet -->
                <div v-else class="flex items-center justify-center px-4 py-16">
                    <div class="max-w-2xl text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 dark:bg-yellow-900/20 rounded-full mb-4">
                            <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-2">Page Not Published</h1>
                        <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                            This page exists but hasn't been published yet. The content is still in draft mode.
                        </p>
                        <p class="text-sm text-neutral-500 dark:text-neutral-500">
                            If you're an administrator, please publish this page from the admin panel.
                        </p>
                    </div>
                </div>
            </template>
        </div>
    </PageLayout>
</template>
