<script setup lang="ts">
import PageLayout from '@/layouts/PageLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { Component } from 'vue';
import { defineAsyncComponent } from 'vue';

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
        layout: string;
        sections: BlockConfig[];
    };
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
}

interface Props {
    page: PageData;
}

const _props = defineProps<Props>();

// Map block types to their Vue components
const blockComponents: Record<string, Component> = {
    hero: defineAsyncComponent(
        () => import('@/components/pagebuilder/blocks/HeroBlock.vue'),
    ),
    features: defineAsyncComponent(
        () => import('@/components/pagebuilder/blocks/FeaturesBlock.vue'),
    ),
    'contact-form': defineAsyncComponent(
        () => import('@/components/pagebuilder/blocks/ContactFormBlock.vue'),
    ),
    cta: defineAsyncComponent(
        () => import('@/components/pagebuilder/blocks/CTABlock.vue'),
    ),
    faq: defineAsyncComponent(
        () => import('@/components/pagebuilder/blocks/FAQBlock.vue'),
    ),
    stats: defineAsyncComponent(
        () => import('@/components/pagebuilder/blocks/StatsBlock.vue'),
    ),
    'legal-text': defineAsyncComponent(
        () => import('@/components/pagebuilder/blocks/LegalTextBlock.vue'),
    ),
    team: defineAsyncComponent(
        () => import('@/components/pagebuilder/blocks/TeamBlock.vue'),
    ),
};

// Get component for a block type
const getBlockComponent = (type: string): Component | null => {
    return blockComponents[type] || null;
};
</script>

<template>
    <PageLayout>
        <Head :title="page.meta_title || page.title">
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
            <!-- Render each section/block -->
            <template v-for="section in page.data.sections" :key="section.id">
                <component
                    :is="getBlockComponent(section.type)"
                    v-if="getBlockComponent(section.type)"
                    :config="section.config"
                />
                <!-- Fallback for unknown block types -->
                <div
                    v-else
                    class="border-l-4 border-yellow-400 bg-yellow-50 px-4 py-8 dark:bg-yellow-900/20"
                >
                    <p class="text-yellow-800 dark:text-yellow-200">
                        Unknown block type: <strong>{{ section.type }}</strong>
                    </p>
                </div>
            </template>
        </div>
    </PageLayout>
</template>
