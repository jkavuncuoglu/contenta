<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Props {
    config: {
        title?: string;
        description?: string;
        buttonText?: string;
        buttonUrl?: string;
        backgroundColor?: string;
        pattern?: 'gradient' | 'solid' | 'bordered';
    };
}

const props = withDefaults(defineProps<Props>(), {
    config: () => ({
        title: 'Ready to Get Started?',
        description: 'Join thousands of satisfied customers today',
        buttonText: 'Get Started Now',
        buttonUrl: '#',
        backgroundColor: 'bg-blue-600',
        pattern: 'gradient',
    }),
});

const patternClass = {
    gradient: 'bg-gradient-to-r from-blue-600 to-purple-600',
    solid: props.config.backgroundColor,
    bordered: 'bg-white dark:bg-gray-900 border-2 border-blue-600',
}[props.config.pattern || 'gradient'];

const textColorClass =
    props.config.pattern === 'bordered'
        ? 'text-gray-900 dark:text-white'
        : 'text-white';
</script>

<template>
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div
                class="mx-auto max-w-4xl rounded-2xl p-12 text-center"
                :class="patternClass"
            >
                <h2
                    class="mb-4 text-3xl font-bold md:text-4xl"
                    :class="textColorClass"
                >
                    {{ config.title }}
                </h2>
                <p class="mb-8 text-lg md:text-xl" :class="textColorClass">
                    {{ config.description }}
                </p>
                <Link
                    :href="config.buttonUrl || '#'"
                    :class="
                        config.pattern === 'bordered'
                            ? 'bg-blue-600 text-white hover:bg-blue-700'
                            : 'bg-white text-blue-600 hover:bg-gray-100'
                    "
                    class="inline-flex rounded-lg px-8 py-3 font-semibold shadow-lg transition-colors hover:shadow-xl"
                >
                    {{ config.buttonText }}
                </Link>
            </div>
        </div>
    </section>
</template>
