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
</script>

<template>
  <section class="py-16" :class="config.backgroundColor">
    <div class="container mx-auto px-4">
      <h2 v-if="config.title" class="text-3xl md:text-4xl font-bold text-center text-gray-900 dark:text-white mb-12">
        {{ config.title }}
      </h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-5xl mx-auto">
        <div
          v-for="(stat, index) in config.stats"
          :key="index"
          class="text-center"
        >
          <div class="text-4xl md:text-5xl font-bold text-blue-600 dark:text-blue-400 mb-2">
            {{ stat.value }}
          </div>
          <div class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ stat.label }}
          </div>
          <div v-if="stat.description" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            {{ stat.description }}
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
