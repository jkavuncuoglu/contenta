<template>
  <div class="relative flex rounded-lg border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
    <!-- Line Numbers -->
    <div class="flex-shrink-0 border-r border-neutral-200 bg-neutral-50 px-3 py-4 text-right font-mono text-sm leading-6 text-neutral-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-400">
      <div v-for="n in lineCount" :key="n" class="h-6">
        {{ n }}
      </div>
    </div>

    <!-- Editor Textarea -->
    <textarea
      ref="textareaRef"
      v-model="localContent"
      class="min-h-[600px] flex-1 resize-none border-0 bg-transparent px-4 py-4 font-mono text-sm leading-6 text-neutral-900 focus:outline-none focus:ring-0 dark:text-neutral-100"
      :placeholder="placeholder"
      @input="handleInput"
      @scroll="syncScroll"
    ></textarea>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'

interface Props {
  modelValue: string
  placeholder?: string
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Start writing markdown...',
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const textareaRef = ref<HTMLTextAreaElement | null>(null)
const localContent = ref(props.modelValue)

// Calculate line count
const lineCount = computed(() => {
  const lines = localContent.value.split('\n').length
  return Math.max(lines, 20) // Minimum 20 lines visible
})

// Watch for external changes
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue !== localContent.value) {
      localContent.value = newValue
    }
  },
)

// Handle input changes
function handleInput() {
  emit('update:modelValue', localContent.value)
}

// Sync scroll between line numbers and textarea (future enhancement)
function syncScroll() {
  // Can be enhanced to sync line numbers container with textarea scroll
}

// Expose method to focus the editor
defineExpose({
  focus: () => {
    textareaRef.value?.focus()
  },
})
</script>

<style scoped>
/* Remove default textarea styling */
textarea {
  tab-size: 2;
}

/* Custom scrollbar for dark mode */
textarea::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

textarea::-webkit-scrollbar-track {
  background: transparent;
}

textarea::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 4px;
}

textarea::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.3);
}

.dark textarea::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
}

.dark textarea::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>
