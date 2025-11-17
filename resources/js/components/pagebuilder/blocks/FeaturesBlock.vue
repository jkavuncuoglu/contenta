<script setup lang="ts">
import { Icon } from '@iconify/vue';

interface Feature {
    icon: string;
    title: string;
    description: string;
}

interface Props {
    config: {
        title?: string;
        subtitle?: string;
        features?: Feature[];
        columns?: 2 | 3 | 4;
        backgroundColor?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    config: () => ({
        title: 'Our Features',
        subtitle: 'Everything you need to succeed',
        features: [
            {
                icon: 'ph:check-circle',
                title: 'Feature 1',
                description: 'Description of feature 1',
            },
            {
                icon: 'ph:check-circle',
                title: 'Feature 2',
                description: 'Description of feature 2',
            },
            {
                icon: 'ph:check-circle',
                title: 'Feature 3',
                description: 'Description of feature 3',
            },
        ],
        columns: 3,
        backgroundColor: 'bg-white dark:bg-gray-900',
    }),
});

const gridClass = {
    2: 'md:grid-cols-2',
    3: 'md:grid-cols-3',
    4: 'md:grid-cols-2 lg:grid-cols-4',
}[props.config.columns || 3];
</script>

<template>
    <section class="py-16" :class="config.backgroundColor">
        <div class="container mx-auto px-4">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2
                    class="mb-4 text-3xl font-bold text-gray-900 md:text-4xl dark:text-white"
                >
                    {{ config.title }}
                </h2>
                <p
                    v-if="config.subtitle"
                    class="text-lg text-gray-600 dark:text-gray-300"
                >
                    {{ config.subtitle }}
                </p>
            </div>
            <div
                class="mx-auto grid max-w-6xl grid-cols-1 gap-8"
                :class="gridClass"
            >
                <div
                    v-for="(feature, index) in config.features"
                    :key="index"
                    class="rounded-xl bg-white p-8 shadow-sm transition-shadow hover:shadow-md dark:bg-gray-800"
                >
                    <div
                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30"
                    >
                        <Icon
                            :icon="feature.icon"
                            class="h-6 w-6 text-blue-600 dark:text-blue-400"
                        />
                    </div>
                    <h3
                        class="mb-2 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ feature.title }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ feature.description }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
