<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Icon } from '@iconify/vue';
import { useSidebar } from './utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()

const { toggleSidebar } = useSidebar()
</script>

<template>
  <Button
    data-sidebar="trigger"
    data-slot="sidebar-trigger"
    variant="ghost"
    size="icon"
    :class="cn('h-12 w-12', props.class)"
    @click="toggleSidebar"
  >
    <Icon icon="material-symbols-light:menu-open" class="text-neutral-900 dark:text-neutral-50 w-12 h-12" />
    <span class="sr-only">Toggle Sidebar</span>
  </Button>
</template>
