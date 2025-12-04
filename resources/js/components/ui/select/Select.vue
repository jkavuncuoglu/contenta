<script setup lang="ts">
import { type HTMLAttributes, provide } from 'vue'
import { SelectRoot, type SelectRootEmits, type SelectRootProps, useForwardPropsEmits } from 'reka-ui'

const props = defineProps<SelectRootProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<SelectRootEmits>()

const forwarded = useForwardPropsEmits(props, emits)

provide('selectModelValue', props.modelValue)
</script>

<template>
  <SelectRoot v-bind="forwarded">
    <slot />
  </SelectRoot>
</template>
