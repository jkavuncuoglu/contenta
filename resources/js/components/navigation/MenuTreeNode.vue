<template>
  <div class="menu-tree-item" :data-item-id="item.id" :style="{ paddingLeft: `${level * 24}px` }">
    <div
      class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-blue-500 dark:hover:border-blue-500 transition-colors group cursor-move"
      :class="{ 'opacity-50': !item.is_visible }"
    >
      <!-- Drag Handle -->
      <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </div>

      <!-- Expand/Collapse Toggle -->
      <button
        v-if="item.children && item.children.length > 0"
        @click="expanded = !expanded"
        class="flex-shrink-0 w-5 h-5 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
      >
        <svg
          class="w-5 h-5 transition-transform"
          :class="{ 'rotate-90': expanded }"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
      <div v-else class="w-5"></div>

      <!-- Icon -->
      <div v-if="item.icon" class="flex-shrink-0 text-gray-500 dark:text-gray-400" v-html="item.icon"></div>

      <!-- Title and URL -->
      <div class="flex-1 min-w-0">
        <div class="font-medium text-gray-900 dark:text-white truncate">
          {{ item.title }}
        </div>
        <div v-if="item.url" class="text-xs text-gray-500 dark:text-gray-400 truncate">
          {{ item.url }}
        </div>
      </div>

      <!-- Type Badge -->
      <span
        class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
        :class="{
          'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': item.type === 'page',
          'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200': item.type === 'post',
          'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': item.type === 'category',
          'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': item.type === 'tag',
          'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200': item.type === 'custom',
        }"
      >
        {{ item.type }}
      </span>

      <!-- Nesting Controls -->
      <div class="flex-shrink-0 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
        <!-- Outdent (move left) -->
        <button
          v-if="level > 0"
          @click.stop="$emit('outdent', item.id)"
          class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          title="Move Left (Outdent)"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
          </svg>
        </button>

        <!-- Indent (move right) -->
        <button
          v-if="canIndent"
          @click.stop="$emit('indent', item.id)"
          class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          title="Move Right (Indent)"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <!-- Actions -->
      <div class="flex-shrink-0 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
        <button
          @click.stop="$emit('toggle-visibility', item.id)"
          class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          :title="item.is_visible ? 'Hide' : 'Show'"
        >
          <svg v-if="item.is_visible" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
          </svg>
        </button>
        <button
          @click.stop="$emit('edit', item)"
          class="p-1 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
          title="Edit"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
        </button>
        <button
          @click.stop="$emit('delete', item.id)"
          class="p-1 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
          title="Delete"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Children -->
    <div v-if="expanded && item.children && item.children.length > 0" class="mt-2 menu-tree-children">
      <MenuTreeNode
        v-for="(child, index) in item.children"
        :key="child.id"
        :item="child"
        :level="level + 1"
        :is-first="index === 0"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
        @toggle-visibility="$emit('toggle-visibility', $event)"
        @indent="$emit('indent', $event)"
        @outdent="$emit('outdent', $event)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

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
  children: MenuItem[]
}

interface Props {
  item: MenuItem
  level: number
  isFirst?: boolean
}

interface Emits {
  (e: 'edit', item: MenuItem): void
  (e: 'delete', itemId: number): void
  (e: 'toggle-visibility', itemId: number): void
  (e: 'indent', itemId: number): void
  (e: 'outdent', itemId: number): void
}

const props = defineProps<Props>()
defineEmits<Emits>()

const expanded = ref(true)

// Can indent if it's not the first item (so there's a previous sibling to become a child of)
const canIndent = computed(() => !props.isFirst && props.level < 3) // Max 3 levels deep
</script>

<style scoped>
.menu-tree-item {
  @apply transition-all;
}

.rotate-90 {
  transform: rotate(90deg);
}

/* Prevent children container from interfering with parent drag operations */
.menu-tree-children {
  pointer-events: none;
}

/* Re-enable pointer events for the actual children */
.menu-tree-children > * {
  pointer-events: auto;
}
</style>
