<script setup lang="ts">
import { cn } from '@/lib/utils'
import { AlertDialogDescription } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <AlertDialogDescription
    data-slot="alert-dialog-description"
    v-bind="$attrs"
    :class="cn('text-muted-foreground text-sm', props.class)"
  >
    <slot />
  </AlertDialogDescription>
</template>

