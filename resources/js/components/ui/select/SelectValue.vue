<script setup lang="ts">
import { type HTMLAttributes } from 'vue'
import { SelectValue, type SelectValueProps } from 'reka-ui'
import { cn } from '@/lib/utils'

const props = defineProps<SelectValueProps & { class?: HTMLAttributes['class'] }>()
</script>

<template>
  <SelectValue v-bind="props" :class="cn('pointer-events-none', props.class)">
    <slot />
  </SelectValue>
</template>
