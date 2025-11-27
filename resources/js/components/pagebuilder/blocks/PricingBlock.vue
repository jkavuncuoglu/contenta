<script setup lang="ts">
import { computed } from 'vue';

interface PricingTier {
    name: string;
    price: string;
    period?: string;
    description?: string;
    features?: string[];
    highlighted?: boolean;
    buttonText?: string;
    buttonUrl?: string;
}

interface Props {
    config: {
        title?: string;
        subtitle?: string;
        tiers?: PricingTier[];
        backgroundColor?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    config: () => ({
        title: 'Pricing Plans',
        subtitle: 'Choose the perfect plan for your needs',
        tiers: [
            {
                name: 'Basic',
                price: '$9',
                period: 'per month',
                description: 'Perfect for individuals',
                features: ['Feature 1', 'Feature 2', 'Feature 3'],
                highlighted: false,
                buttonText: 'Get Started',
                buttonUrl: '#',
            },
            {
                name: 'Pro',
                price: '$29',
                period: 'per month',
                description: 'Best for professionals',
                features: ['All Basic features', 'Feature 4', 'Feature 5', 'Feature 6'],
                highlighted: true,
                buttonText: 'Get Started',
                buttonUrl: '#',
            },
            {
                name: 'Enterprise',
                price: '$99',
                period: 'per month',
                description: 'For large teams',
                features: ['All Pro features', 'Feature 7', 'Feature 8', 'Feature 9', 'Feature 10'],
                highlighted: false,
                buttonText: 'Contact Us',
                buttonUrl: '#',
            },
        ],
        backgroundColor: 'bg-gray-50 dark:bg-gray-900',
    }),
});

// Handle tiers being either an array or JSON string
const normalizedTiers = computed(() => {
    if (!props.config.tiers) return [];

    // If it's already an array, return it
    if (Array.isArray(props.config.tiers)) {
        return props.config.tiers;
    }

    // If it's a string, try to parse it as JSON
    if (typeof props.config.tiers === 'string') {
        try {
            const parsed = JSON.parse(props.config.tiers);
            if (Array.isArray(parsed)) return parsed;
        } catch {
            // If parsing fails, return empty array
            return [];
        }
    }

    return [];
});
</script>

<template>
    <section class="py-16" :class="config.backgroundColor">
        <div class="container mx-auto px-4">
            <div v-if="config.title || config.subtitle" class="mx-auto mb-12 max-w-3xl text-center">
                <h2
                    v-if="config.title"
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

            <div class="mx-auto grid max-w-6xl grid-cols-1 gap-8 md:grid-cols-3">
                <div
                    v-for="(tier, index) in normalizedTiers"
                    :key="index"
                    class="relative flex flex-col rounded-2xl bg-white p-8 shadow-lg transition-all hover:shadow-xl dark:bg-gray-800"
                    :class="{
                        'ring-2 ring-blue-600 scale-105': tier.highlighted,
                    }"
                >
                    <!-- Highlighted badge -->
                    <div
                        v-if="tier.highlighted"
                        class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-blue-600 px-4 py-1 text-sm font-semibold text-white"
                    >
                        Most Popular
                    </div>

                    <!-- Tier Header -->
                    <div class="mb-6">
                        <h3 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ tier.name }}
                        </h3>
                        <p
                            v-if="tier.description"
                            class="text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{ tier.description }}
                        </p>
                    </div>

                    <!-- Price -->
                    <div class="mb-6">
                        <div class="flex items-baseline">
                            <span class="text-5xl font-bold text-gray-900 dark:text-white">
                                {{ tier.price }}
                            </span>
                            <span
                                v-if="tier.period"
                                class="ml-2 text-gray-600 dark:text-gray-400"
                            >
                                {{ tier.period }}
                            </span>
                        </div>
                    </div>

                    <!-- Features -->
                    <ul class="mb-8 flex-1 space-y-3">
                        <li
                            v-for="(feature, featureIndex) in tier.features"
                            :key="featureIndex"
                            class="flex items-start"
                        >
                            <svg
                                class="mr-3 h-5 w-5 flex-shrink-0 text-green-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                ></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">
                                {{ feature }}
                            </span>
                        </li>
                    </ul>

                    <!-- CTA Button -->
                    <a
                        :href="tier.buttonUrl || '#'"
                        class="block rounded-lg px-6 py-3 text-center font-semibold transition-colors"
                        :class="{
                            'bg-blue-600 text-white hover:bg-blue-700': tier.highlighted,
                            'border-2 border-blue-600 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-900/20': !tier.highlighted,
                        }"
                    >
                        {{ tier.buttonText || 'Get Started' }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>
