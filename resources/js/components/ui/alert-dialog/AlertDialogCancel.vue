<script setup lang="ts">
import { cn } from '@/lib/utils'
import { AlertDialogCancel } from 'reka-ui'
import { type HTMLAttributes } from 'vue'
import { buttonVariants } from '../button'

const props = defineProps<{
  class?: HTMLAttributes['class']
  asChild?: boolean
}>()
</script>

<template>
  <AlertDialogCancel
    data-slot="alert-dialog-cancel"
    v-bind="$attrs"
    :class="cn(buttonVariants({ variant: 'outline' }), 'mt-2 sm:mt-0', props.class)"
  >
    <slot />
  </AlertDialogCancel>
</template>

