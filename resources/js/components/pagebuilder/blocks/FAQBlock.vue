<script setup lang="ts">
import { ref } from 'vue';
import { Icon } from '@iconify/vue';

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

withDefaults(defineProps<Props>(), {
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

const openIndex = ref<number | null>(null);

const toggle = (index: number) => {
  openIndex.value = openIndex.value === index ? null : index;
};
</script>

<template>
  <section class="py-16" :class="config.backgroundColor">
    <div class="container mx-auto px-4">
      <div class="max-w-3xl mx-auto text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
          {{ config.title }}
        </h2>
        <p v-if="config.subtitle" class="text-lg text-gray-600 dark:text-gray-300">
          {{ config.subtitle }}
        </p>
      </div>
      <div class="max-w-3xl mx-auto space-y-4">
        <div
          v-for="(item, index) in config.items"
          :key="index"
          class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden"
        >
          <button
            @click="toggle(index)"
            class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
          >
            <span class="font-semibold text-gray-900 dark:text-white">{{ item.question }}</span>
            <Icon
              icon="ph:caret-down"
              class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform"
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
            <div v-if="openIndex === index" class="px-6 pb-4 text-gray-600 dark:text-gray-300">
              {{ item.answer }}
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </section>
</template>
