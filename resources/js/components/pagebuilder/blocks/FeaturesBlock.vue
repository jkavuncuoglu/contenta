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
      <div class="max-w-3xl mx-auto text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
          {{ config.title }}
        </h2>
        <p v-if="config.subtitle" class="text-lg text-gray-600 dark:text-gray-300">
          {{ config.subtitle }}
        </p>
      </div>
      <div class="grid grid-cols-1 gap-8 max-w-6xl mx-auto" :class="gridClass">
        <div
          v-for="(feature, index) in config.features"
          :key="index"
          class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm hover:shadow-md transition-shadow"
        >
          <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4">
            <Icon :icon="feature.icon" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
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
