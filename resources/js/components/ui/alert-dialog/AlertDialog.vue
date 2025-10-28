<script setup lang="ts">
import { AlertDialogRoot } from 'reka-ui'

const props = defineProps<{
  open?: boolean
  defaultOpen?: boolean
}>()

const emit = defineEmits<{
  'update:open': [value: boolean]
}>()
</script>

<template>
  <AlertDialogRoot
    data-slot="alert-dialog"
    v-bind="$attrs"
    :open="props.open"
    :default-open="props.defaultOpen"
    @update:open="emit('update:open', $event)"
  >
    <slot />
  </AlertDialogRoot>
</template>
