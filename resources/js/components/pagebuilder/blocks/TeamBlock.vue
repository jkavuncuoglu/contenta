<script setup lang="ts">
interface TeamMember {
  name: string;
  role: string;
  bio?: string;
  image?: string;
}

interface Props {
  config: {
    title?: string;
    subtitle?: string;
    members?: TeamMember[];
    columns?: 2 | 3 | 4;
    backgroundColor?: string;
  };
}

const props = withDefaults(defineProps<Props>(), {
  config: () => ({
    title: 'Meet Our Team',
    subtitle: 'The people behind our success',
    members: [
      {
        name: 'John Doe',
        role: 'CEO & Founder',
        bio: 'Leading the company with vision and passion.',
        image: '',
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
          v-for="(member, index) in config.members"
          :key="index"
          class="text-center"
        >
          <div class="mb-4 mx-auto w-32 h-32 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
            <img
              v-if="member.image"
              :src="member.image"
              :alt="member.name"
              class="w-full h-full object-cover"
            />
            <span v-else class="text-4xl font-bold text-gray-400 dark:text-gray-500">
              {{ member.name.charAt(0) }}
            </span>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
            {{ member.name }}
          </h3>
          <p class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-2">
            {{ member.role }}
          </p>
          <p v-if="member.bio" class="text-gray-600 dark:text-gray-400">
            {{ member.bio }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
