<script setup lang="ts">
import { computed } from 'vue';

interface Stat {
    value: string;
    label: string;
    description?: string;
}

interface Props {
    config: {
        title?: string;
        stats?: Stat[] | string;
        backgroundColor?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
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

// Handle stats being either an array or comma-separated string
const normalizedStats = computed(() => {
    if (!props.config.stats) return [];

    // If it's already an array, return it
    if (Array.isArray(props.config.stats)) {
        return props.config.stats;
    }

    // If it's a string, try to parse it
    if (typeof props.config.stats === 'string') {
        try {
            // Try parsing as JSON first
            const parsed = JSON.parse(props.config.stats);
            if (Array.isArray(parsed)) return parsed;
        } catch {
            // If not JSON, treat as comma-separated values
            // Format: "value:label, value:label"
            return props.config.stats.split(',').map(stat => {
                const [value, label, description] = stat.trim().split(':');
                return {
                    value: value?.trim() || '',
                    label: label?.trim() || '',
                    description: description?.trim()
                };
            }).filter(stat => stat.value && stat.label);
        }
    }

    return [];
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
                    v-for="(stat, index) in normalizedStats"
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
