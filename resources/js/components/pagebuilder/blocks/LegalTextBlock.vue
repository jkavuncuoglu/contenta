<script setup lang="ts">
interface Props {
    config: {
        title?: string;
        lastUpdated?: string;
        content?: string;
        sections?: Array<{
            heading: string;
            content: string;
        }>;
    };
}

withDefaults(defineProps<Props>(), {
    config: () => ({
        title: 'Legal Document',
        lastUpdated: new Date().toLocaleDateString(),
        content: '',
        sections: [],
    }),
});
</script>

<template>
    <section class="bg-white py-16 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-4xl">
                <h1
                    class="mb-4 text-4xl font-bold text-gray-900 md:text-5xl dark:text-white"
                >
                    {{ config.title }}
                </h1>
                <p class="mb-8 text-sm text-gray-600 dark:text-gray-400">
                    Last updated: {{ config.lastUpdated }}
                </p>

                <div
                    v-if="config.content"
                    class="prose dark:prose-invert mb-8 max-w-none"
                >
                    <p class="leading-relaxed text-gray-700 dark:text-gray-300">
                        {{ config.content }}
                    </p>
                </div>

                <div
                    v-if="config.sections && config.sections.length > 0"
                    class="space-y-8"
                >
                    <div
                        v-for="(section, index) in config.sections"
                        :key="index"
                        class="border-l-4 border-blue-600 pl-6"
                    >
                        <h2
                            class="mb-4 text-2xl font-bold text-gray-900 dark:text-white"
                        >
                            {{ section.heading }}
                        </h2>
                        <div class="prose dark:prose-invert max-w-none">
                            <p
                                class="leading-relaxed whitespace-pre-line text-gray-700 dark:text-gray-300"
                            >
                                {{ section.content }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
