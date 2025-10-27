<script setup lang="ts">
import { cn } from '@/lib/utils'
import { AlertDialogTitle } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <AlertDialogTitle
    data-slot="alert-dialog-title"
    v-bind="$attrs"
    :class="cn('text-lg leading-none font-semibold', props.class)"
  >
    <slot />
  </AlertDialogTitle>
</template>

