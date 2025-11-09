<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Live Preview</h3>
        <div class="flex items-center gap-2">
          <button
            @click="viewMode = 'desktop'"
            :class="{
              'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300': viewMode === 'desktop',
              'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400': viewMode !== 'desktop',
            }"
            class="px-3 py-1 rounded-md text-sm font-medium transition-colors"
            title="Desktop View"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </button>
          <button
            @click="viewMode = 'mobile'"
            :class="{
              'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300': viewMode === 'mobile',
              'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400': viewMode !== 'mobile',
            }"
            class="px-3 py-1 rounded-md text-sm font-medium transition-colors"
            title="Mobile View"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div class="p-6">
      <!-- Desktop Preview -->
      <div
        v-if="viewMode === 'desktop'"
        class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700"
      >
        <nav class="preview-nav">
          <ul class="flex flex-wrap gap-1">
            <MenuPreviewItem
              v-for="item in visibleItems"
              :key="item.id"
              :item="item"
              :level="0"
              mode="desktop"
            />
          </ul>
        </nav>

        <!-- Empty State -->
        <div v-if="visibleItems.length === 0" class="text-center py-8">
          <p class="text-sm text-gray-500 dark:text-gray-400">
            No visible menu items to preview
          </p>
        </div>
      </div>

      <!-- Mobile Preview -->
      <div
        v-if="viewMode === 'mobile'"
        class="mx-auto bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700"
        style="max-width: 375px"
      >
        <nav class="preview-nav-mobile">
          <ul class="space-y-1">
            <MenuPreviewItem
              v-for="item in visibleItems"
              :key="item.id"
              :item="item"
              :level="0"
              mode="mobile"
            />
          </ul>
        </nav>

        <!-- Empty State -->
        <div v-if="visibleItems.length === 0" class="text-center py-8">
          <p class="text-sm text-gray-500 dark:text-gray-400">
            No visible menu items to preview
          </p>
        </div>
      </div>

      <!-- Preview Note -->
      <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
        <p class="text-xs text-blue-800 dark:text-blue-300">
          <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          This is a simplified preview. Actual styling may vary based on your theme.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import MenuPreviewItem from './MenuPreviewItem.vue'

interface MenuItem {
  id: number
  title: string
  url: string | null
  type: string
  target: string
  css_classes: string | null
  icon: string | null
  is_visible: boolean
  attributes: Record<string, any> | null
  metadata: Record<string, any> | null
  children?: MenuItem[]
}

interface Props {
  items: MenuItem[]
}

const props = defineProps<Props>()

const viewMode = ref<'desktop' | 'mobile'>('desktop')

const visibleItems = computed(() => {
  return props.items.filter(item => item.is_visible)
})
</script>

