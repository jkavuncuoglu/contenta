<template>
  <div class="flex gap-6">
    <!-- Main Editor Area -->
    <div class="flex-1 space-y-4">
      <!-- Toolbar -->
      <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-neutral-50 p-3 dark:border-neutral-700 dark:bg-neutral-800">
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Layout Template:</span>
            <select
              v-model="layoutTemplate"
              class="rounded border border-neutral-300 bg-white px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
              @change="$emit('update:layout-template', layoutTemplate)"
            >
              <option value="default">Default (Centered Container)</option>
              <option value="full-width">Full Width</option>
              <option value="sidebar-left">Left Sidebar</option>
              <option value="sidebar-right">Right Sidebar</option>
            </select>
          </div>

          <!-- Editor Mode Toggle -->
          <div class="flex items-center gap-2 rounded-md border border-neutral-300 bg-white p-1 dark:border-neutral-600 dark:bg-neutral-700">
            <button
              @click="editorMode = 'visual'"
              :class="[
                'rounded px-3 py-1 text-xs font-medium transition-colors',
                editorMode === 'visual'
                  ? 'bg-blue-600 text-white dark:bg-blue-500'
                  : 'text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-200'
              ]"
            >
              Visual
            </button>
            <button
              @click="editorMode = 'raw'"
              :class="[
                'rounded px-3 py-1 text-xs font-medium transition-colors',
                editorMode === 'raw'
                  ? 'bg-blue-600 text-white dark:bg-blue-500'
                  : 'text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-200'
              ]"
            >
              <span class="flex items-center gap-1">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                Raw
              </span>
            </button>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="showLibrary = !showLibrary"
            class="rounded-md bg-blue-600 px-4 py-1.5 text-sm font-medium text-white transition-colors hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
          >
            {{ showLibrary ? 'Hide' : 'Show' }} Shortcuts
          </button>
        </div>
      </div>

      <!-- Editor -->
      <EditorJSWrapper
        v-if="editorMode === 'visual'"
        ref="editorRef"
        v-model="content"
        placeholder="Start writing your page content with markdown and shortcodes...

Example:
[#hero title=&quot;Welcome&quot; subtitle=&quot;Get Started&quot;]{
Your description here
}[/#hero]

[#features title=&quot;Our Features&quot; columns=&quot;3&quot;]{
[#feature title=&quot;Fast&quot;]{
Lightning-fast performance
}[/#feature]
}[/#features]"
        @update:model-value="handleInput"
      />

      <RawMarkdownEditor
        v-else
        ref="rawEditorRef"
        v-model="content"
        placeholder="Start writing your page content with markdown and shortcodes..."
        @update:model-value="handleInput"
      />

      <!-- Character Count & Syntax Status -->
      <div class="flex items-center justify-between text-sm text-neutral-600 dark:text-neutral-400">
        <div class="flex items-center gap-4">
          <span>{{ content.length.toLocaleString() }} characters</span>
          <span v-if="hasShortcodes" class="flex items-center gap-1 text-blue-600 dark:text-blue-400">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd"
              />
            </svg>
            Shortcodes detected
          </span>
        </div>
        <button
          @click="previewMarkdown"
          class="flex items-center gap-1 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
            />
          </svg>
          Preview
        </button>
      </div>
    </div>

    <!-- Shortcodes Library Sidebar -->
    <transition
      enter-active-class="transition-transform duration-300"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-transform duration-300"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div v-if="showLibrary" class="w-96 overflow-y-auto rounded-lg border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-800" style="max-height: 700px">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Shortcodes Library</h3>
          <button
            @click="showLibrary = false"
            class="text-neutral-400 hover:text-neutral-600 dark:text-neutral-500 dark:hover:text-neutral-300"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <ShortcodesLibrary @insert="insertShortcode" />
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import EditorJSWrapper from './EditorJSWrapper.vue'
import RawMarkdownEditor from './RawMarkdownEditor.vue'
import ShortcodesLibrary from './ShortcodesLibrary.vue'
import { computed, ref, watch } from 'vue'

interface Props {
  modelValue: string
  layoutTemplate?: string
}

const props = withDefaults(defineProps<Props>(), {
  layoutTemplate: 'default',
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'update:layout-template': [value: string]
  preview: []
}>()

const content = ref(props.modelValue)
const editorRef = ref<InstanceType<typeof EditorJSWrapper> | null>(null)
const rawEditorRef = ref<InstanceType<typeof RawMarkdownEditor> | null>(null)
const showLibrary = ref(true)
const layoutTemplate = ref(props.layoutTemplate)
const editorMode = ref<'visual' | 'raw'>('visual')

watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue !== content.value) {
      content.value = newValue
    }
  },
)

watch(
  () => props.layoutTemplate,
  (newValue) => {
    layoutTemplate.value = newValue
  },
)

const hasShortcodes = computed(() => {
  return content.value.includes('[#')
})

function handleInput(value: string) {
  content.value = value
  emit('update:modelValue', value)
}

async function insertShortcode(template: string) {
  if (editorMode.value === 'visual') {
    if (!editorRef.value) return
    // Use the exposed method from EditorJSWrapper
    await editorRef.value.insertShortcode(template)
  } else {
    // Insert into raw markdown editor at current cursor position
    // For now, just append to the end with newlines
    content.value = content.value ? `${content.value}\n\n${template}` : template
    handleInput(content.value)
  }
}

function previewMarkdown() {
  emit('preview')
}
</script>

