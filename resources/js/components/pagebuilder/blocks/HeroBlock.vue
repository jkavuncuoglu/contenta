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
    backgroundColor: 'bg-gradient-to-b from-blue-50 to-white dark:from-gray-900 dark:to-gray-800',
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
    class="relative py-20 lg:py-32 overflow-hidden"
    :class="config.backgroundColor"
    :style="config.backgroundImage ? `background-image: url(${config.backgroundImage}); background-size: cover; background-position: center;` : ''"
  >
    <div class="container mx-auto px-4">
      <div class="max-w-4xl mx-auto flex flex-col" :class="alignmentClass">
        <h2 v-if="config.subtitle" class="text-sm md:text-base font-semibold text-blue-600 dark:text-blue-400 mb-4 uppercase tracking-wide">
          {{ config.subtitle }}
        </h2>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6">
          {{ config.title }}
        </h1>
        <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl">
          {{ config.description }}
        </p>
        <div class="flex items-center gap-4 flex-wrap" :class="alignmentClass">
          <Link
            v-if="config.primaryButtonText"
            :href="config.primaryButtonUrl || '#'"
            class="px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl"
          >
            {{ config.primaryButtonText }}
          </Link>
          <Link
            v-if="config.secondaryButtonText"
            :href="config.secondaryButtonUrl || '#'"
            class="px-8 py-3 border-2 border-blue-600 text-blue-600 dark:text-blue-400 rounded-lg font-semibold hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
          >
            {{ config.secondaryButtonText }}
          </Link>
        </div>
      </div>
    </div>
  </section>
</template>
