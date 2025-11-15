<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Props {
    config: {
        title?: string;
        subtitle?: string;
        description?: string;
        primaryButtonText?: string;
        primaryButtonUrl?: string;
        secondaryButtonText?: string;
        secondaryButtonUrl?: string;
        backgroundImage?: string;
        backgroundColor?: string;
        textAlign?: 'left' | 'center' | 'right';
    };
}

const props = withDefaults(defineProps<Props>(), {
    config: () => ({
        title: 'Welcome to Our Site',
        subtitle: '',
        description: 'Discover amazing content and services',
        primaryButtonText: 'Get Started',
        primaryButtonUrl: '#',
        secondaryButtonText: 'Learn More',
        secondaryButtonUrl: '#',
        backgroundImage: '',
        backgroundColor:
            'bg-gradient-to-b from-blue-50 to-white dark:from-gray-900 dark:to-gray-800',
        textAlign: 'center',
    }),
});

const alignmentClass = {
    left: 'text-left items-start',
    center: 'text-center items-center',
    right: 'text-right items-end',
}[props.config.textAlign || 'center'];
</script>

<template>
    <section
        class="relative overflow-hidden py-20 lg:py-32"
        :class="config.backgroundColor"
        :style="
            config.backgroundImage
                ? `background-image: url(${config.backgroundImage}); background-size: cover; background-position: center;`
                : ''
        "
    >
        <div class="container mx-auto px-4">
            <div
                class="mx-auto flex max-w-4xl flex-col"
                :class="alignmentClass"
            >
                <h2
                    v-if="config.subtitle"
                    class="mb-4 text-sm font-semibold tracking-wide text-blue-600 uppercase md:text-base dark:text-blue-400"
                >
                    {{ config.subtitle }}
                </h2>
                <h1
                    class="mb-6 text-4xl font-bold text-gray-900 md:text-5xl lg:text-6xl dark:text-white"
                >
                    {{ config.title }}
                </h1>
                <p
                    class="mb-8 max-w-2xl text-lg text-gray-600 md:text-xl dark:text-gray-300"
                >
                    {{ config.description }}
                </p>
                <div
                    class="flex flex-wrap items-center gap-4"
                    :class="alignmentClass"
                >
                    <Link
                        v-if="config.primaryButtonText"
                        :href="config.primaryButtonUrl || '#'"
                        class="rounded-lg bg-blue-600 px-8 py-3 font-semibold text-white shadow-lg transition-colors hover:bg-blue-700 hover:shadow-xl"
                    >
                        {{ config.primaryButtonText }}
                    </Link>
                    <Link
                        v-if="config.secondaryButtonText"
                        :href="config.secondaryButtonUrl || '#'"
                        class="rounded-lg border-2 border-blue-600 px-8 py-3 font-semibold text-blue-600 transition-colors hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20"
                    >
                        {{ config.secondaryButtonText }}
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
