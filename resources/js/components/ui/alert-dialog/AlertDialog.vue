<script setup lang="ts">
import { AlertDialogRoot } from 'reka-ui'

defineProps<{
  open?: boolean
  defaultOpen?: boolean
}>()

defineEmits<{
  'update:open': [value: boolean]
}>()
</script>

<template>
  <AlertDialogRoot
    data-slot="alert-dialog"
    v-bind="$attrs"
  >
    <slot />
  </AlertDialogRoot>
</template>

