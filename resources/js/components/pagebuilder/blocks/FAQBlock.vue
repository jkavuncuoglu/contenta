<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { ref } from 'vue';

interface FAQItem {
    question: string;
    answer: string;
}

interface Props {
    config: {
        title?: string;
        subtitle?: string;
        items?: FAQItem[];
        backgroundColor?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    config: () => ({
        title: 'Frequently Asked Questions',
        subtitle: 'Find answers to common questions',
        items: [
            {
                question: 'Question 1?',
                answer: 'Answer to question 1.',
            },
            {
                question: 'Question 2?',
                answer: 'Answer to question 2.',
            },
        ],
        backgroundColor: 'bg-white dark:bg-gray-900',
    }),
});

const _props = props; // retain for potential future use without lint error

const openIndex = ref<number | null>(null);

const toggle = (index: number) => {
    openIndex.value = openIndex.value === index ? null : index;
};
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
            <div class="mx-auto max-w-3xl space-y-4">
                <div
                    v-for="(item, index) in config.items"
                    :key="index"
                    class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800"
                >
                    <button
                        @click="toggle(index)"
                        class="flex w-full items-center justify-between px-6 py-4 text-left transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                    >
                        <span
                            class="font-semibold text-gray-900 dark:text-white"
                            >{{ item.question }}</span
                        >
                        <Icon
                            icon="ph:caret-down"
                            class="h-5 w-5 text-gray-500 transition-transform dark:text-gray-400"
                            :class="{ 'rotate-180': openIndex === index }"
                        />
                    </button>
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-2"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-2"
                    >
                        <div
                            v-if="openIndex === index"
                            class="px-6 pb-4 text-gray-600 dark:text-gray-300"
                        >
                            {{ item.answer }}
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </section>
</template>
