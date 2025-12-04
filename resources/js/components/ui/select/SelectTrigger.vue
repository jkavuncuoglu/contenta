<script setup lang="ts">
import { type HTMLAttributes, computed } from 'vue'
import { SelectIcon, SelectTrigger, type SelectTriggerProps, useForwardProps } from 'reka-ui'
import { Icon } from '@iconify/vue'
import { cn } from '@/lib/utils'

const props = defineProps<SelectTriggerProps & { class?: HTMLAttributes['class'] }>()

// Strip class from delegated props and forward the rest using the helper
const delegated = computed(() => {
  const { class: _, ...rest } = props as any
  return rest
})

const forwarded = useForwardProps(delegated)
</script>

<template>
  <SelectTrigger
    v-bind="forwarded"
    :class="cn(
      'border-input bg-background ring-offset-background placeholder:text-muted-foreground focus:ring-ring data-[placeholder]:text-muted-foreground flex h-9 w-full items-center justify-between whitespace-nowrap rounded-md border px-3 py-2 text-sm shadow-xs focus:outline-none focus:ring-1 disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1',
      props.class,
    )"
  >
    <slot />
    <SelectIcon as-child>
      <Icon icon="lucide:chevron-down" class="h-4 w-4 opacity-50" />
    </SelectIcon>
  </SelectTrigger>
</template>
