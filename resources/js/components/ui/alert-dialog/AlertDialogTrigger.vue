<script setup lang="ts">
import { AlertDialogTrigger } from 'reka-ui'

defineProps<{
  asChild?: boolean
}>()
</script>

<template>
  <AlertDialogTrigger
    data-slot="alert-dialog-trigger"
    v-bind="$attrs"
  >
    <slot />
  </AlertDialogTrigger>
</template>

