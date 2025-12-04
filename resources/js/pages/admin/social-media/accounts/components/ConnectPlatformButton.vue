<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

interface Props {
  platform: string
  name: string
  icon: string
  color: string
}

const props = defineProps<Props>()

const handleConnect = () => {
  router.visit(`/admin/social-media/oauth/${props.platform}/authorize`, {
    method: 'get',
  })
}
</script>

<template>
  <button
    type="button"
    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:shadow focus:outline-none focus:ring-2 focus:ring-offset-2"
    :style="{ '--platform-color': color }"
    @click="handleConnect"
  >
    <Icon :icon="icon" class="h-5 w-5" :style="{ color: color }" />
    <span>Connect {{ name }}</span>
  </button>
</template>

<style scoped>
button:hover {
  border-color: var(--platform-color);
}

button:focus {
  --tw-ring-color: var(--platform-color);
}
</style>
