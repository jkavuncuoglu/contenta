<script setup lang="ts">
interface Stat {
    value: string;
    label: string;
    description?: string;
}

interface Props {
    config: {
        title?: string;
        stats?: Stat[];
        backgroundColor?: string;
    };
}

withDefaults(defineProps<Props>(), {
    config: () => ({
        title: 'Our Impact',
        stats: [
            { value: '10K+', label: 'Customers', description: 'Trust us' },
            { value: '98%', label: 'Satisfaction', description: 'Rating' },
            { value: '24/7', label: 'Support', description: 'Available' },
            { value: '50+', label: 'Countries', description: 'Worldwide' },
        ],
        backgroundColor: 'bg-gray-50 dark:bg-gray-800',
    }),
});
</script>

<template>
    <section class="py-16" :class="config.backgroundColor">
        <div class="container mx-auto px-4">
            <h2
                v-if="config.title"
                class="mb-12 text-center text-3xl font-bold text-gray-900 md:text-4xl dark:text-white"
            >
                {{ config.title }}
            </h2>
            <div
                class="mx-auto grid max-w-5xl grid-cols-2 gap-8 md:grid-cols-4"
            >
                <div
                    v-for="(stat, index) in config.stats"
                    :key="index"
                    class="text-center"
                >
                    <div
                        class="mb-2 text-4xl font-bold text-blue-600 md:text-5xl dark:text-blue-400"
                    >
                        {{ stat.value }}
                    </div>
                    <div
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                    >
                        {{ stat.label }}
                    </div>
                    <div
                        v-if="stat.description"
                        class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                    >
                        {{ stat.description }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
