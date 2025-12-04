<script setup lang="ts">
import { type HTMLAttributes } from 'vue'
import { SelectItem, SelectItemIndicator, type SelectItemProps, SelectItemText } from 'reka-ui'
import { Icon } from '@iconify/vue'
import { cn } from '@/lib/utils'

const props = defineProps<SelectItemProps & { class?: HTMLAttributes['class'] }>()
</script>

<template>
  <SelectItem
    v-bind="props"
    :class="cn(
      'focus:bg-accent focus:text-accent-foreground relative flex w-full cursor-default select-none items-start rounded-sm py-1.5 pl-2 pr-8 text-sm outline-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
      props.class,
    )"
  >
    <span class="absolute right-2 top-2 flex h-3.5 w-3.5 items-center justify-center">
      <SelectItemIndicator>
        <Icon icon="lucide:check" class="h-4 w-4" />
      </SelectItemIndicator>
    </span>
    <!-- Label (this will be used by SelectValue / trigger) -->
    <div class="flex w-full flex-col">
      <SelectItemText>
        <slot />
      </SelectItemText>

      <!-- Optional named slot for description shown only in the dropdown -->
      <div class="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">
        <slot name="description" />
      </div>
    </div>
  </SelectItem>
</template>
