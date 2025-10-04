<template>
  <div>
    <!-- Simple textarea-based editor as a stub for the real component -->
    <textarea
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      class="w-full min-h-[200px] p-2 border rounded dark:bg-gray-800 dark:text-white"
    />
  </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue';

defineProps<{ modelValue?: string }>();
</script>
