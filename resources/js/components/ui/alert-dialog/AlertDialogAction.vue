<script setup lang="ts">
import { cn } from '@/lib/utils'
import { AlertDialogAction } from 'reka-ui'
import { type HTMLAttributes } from 'vue'
import { buttonVariants } from '../button'

const props = defineProps<{
  class?: HTMLAttributes['class']
  asChild?: boolean
}>()
</script>

<template>
  <AlertDialogAction
    data-slot="alert-dialog-action"
    v-bind="$attrs"
    :class="cn(buttonVariants(), props.class)"
  >
    <slot />
  </AlertDialogAction>
</template>

