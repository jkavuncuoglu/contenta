<script setup lang="ts">
interface Props {
  config: {
    title?: string;
    lastUpdated?: string;
    content?: string;
    sections?: Array<{
      heading: string;
      content: string;
    }>;
  };
}

const props = withDefaults(defineProps<Props>(), {
  config: () => ({
    title: 'Legal Document',
    lastUpdated: new Date().toLocaleDateString(),
    content: '',
    sections: [],
  }),
});
</script>

<template>
  <section class="py-16 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
          {{ config.title }}
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-8">
          Last updated: {{ config.lastUpdated }}
        </p>

        <div v-if="config.content" class="prose dark:prose-invert max-w-none mb-8">
          <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
            {{ config.content }}
          </p>
        </div>

        <div v-if="config.sections && config.sections.length > 0" class="space-y-8">
          <div
            v-for="(section, index) in config.sections"
            :key="index"
            class="border-l-4 border-blue-600 pl-6"
          >
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
              {{ section.heading }}
            </h2>
            <div class="prose dark:prose-invert max-w-none">
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                {{ section.content }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
