<script setup lang="ts">
import { cn } from '@/lib/utils'
import { AlertDialogOverlay } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = defineProps<{
  class?: HTMLAttributes['class']
  forceMount?: boolean
}>()
</script>

<template>
  <AlertDialogOverlay
    data-slot="alert-dialog-overlay"
    v-bind="$attrs"
    :class="cn('data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 fixed inset-0 z-50 bg-black/80', props.class)"
  >
    <slot />
  </AlertDialogOverlay>
</template>

