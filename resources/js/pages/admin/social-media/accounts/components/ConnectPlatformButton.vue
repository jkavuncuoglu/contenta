<script setup lang="ts">
import { Icon } from '@iconify/vue'

interface Props {
  platform: string
  name: string
  icon: string
  color: string
}

const props = defineProps<Props>()
</script>

<template>
  <a
    :href="`/admin/social-media/oauth/${platform}/authorize`"
    class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 bg-white px-4 py-2.5 text-sm font-medium text-neutral-700 shadow-sm transition-all hover:bg-neutral-50 hover:shadow focus:outline-none focus:ring-2 focus:ring-offset-2 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-600"
    :style="{ '--platform-color': color }"
  >
    <Icon :icon="icon" class="h-5 w-5" :style="{ color: color }" />
    <span>Connect {{ name }}</span>
  </a>
</template>

<style scoped>
a:hover {
  border-color: var(--platform-color);
}

a:focus {
  --tw-ring-color: var(--platform-color);
}
</style>
