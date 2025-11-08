<template>
  <div class="menu-tree-view">
    <div v-if="modelValue.length > 0" ref="treeContainer" class="space-y-2">
      <MenuTreeNode
        v-for="item in modelValue"
        :key="item.id"
        :item="item"
        :level="0"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
        @toggle-visibility="toggleVisibility"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, nextTick } from 'vue'
import { dragAndDrop } from '@formkit/drag-and-drop'
import MenuTreeNode from './MenuTreeNode.vue'

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
  modelValue: MenuItem[]
  menuId: number
}

interface Emits {
  (e: 'update:modelValue', value: MenuItem[]): void
  (e: 'update', value: MenuItem[]): void
  (e: 'edit', item: MenuItem): void
  (e: 'delete', itemId: number): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const treeContainer = ref<HTMLElement | null>(null)

const toggleVisibility = async (itemId: number) => {
  const updateVisibility = (items: MenuItem[]): MenuItem[] => {
    return items.map(item => {
      if (item.id === itemId) {
        return { ...item, is_visible: !item.is_visible }
      }
      if (item.children.length > 0) {
        return { ...item, children: updateVisibility(item.children) }
      }
      return item
    })
  }

  const updated = updateVisibility(props.modelValue)
  emit('update:modelValue', updated)
  emit('update', updated)
}

// Initialize drag-and-drop
onMounted(() => {
  if (treeContainer.value) {
    dragAndDrop({
      parent: treeContainer.value,
      draggable: (el) => {
        return el.classList.contains('menu-tree-item')
      },
      accepts: (targetParent, newParent) => {
        // Allow dropping anywhere in the tree
        return newParent.classList.contains('menu-tree-view') ||
               newParent.classList.contains('menu-tree-children')
      },
      plugins: [],
      handleDragstart: (data) => {
        data.e.dataTransfer!.effectAllowed = 'move'
      },
      handleEnd: (data) => {
        // Reconstruct the tree structure from DOM
        nextTick(() => {
          const newStructure = buildTreeFromDOM()
          emit('update:modelValue', newStructure)
          emit('update', newStructure)
        })
      },
    })
  }
})

const buildTreeFromDOM = (): MenuItem[] => {
  if (!treeContainer.value) return props.modelValue

  const result: MenuItem[] = []
  const itemMap = new Map<number, MenuItem>()

  // First, create a map of all items by ID for quick lookup
  const createItemMap = (items: MenuItem[]) => {
    items.forEach(item => {
      itemMap.set(item.id, { ...item, children: [] })
      if (item.children.length > 0) {
        createItemMap(item.children)
      }
    })
  }
  createItemMap(props.modelValue)

  // Get all root-level menu items from DOM
  const rootItems = treeContainer.value.querySelectorAll<HTMLElement>(':scope > .menu-tree-item')

  const processNode = (element: HTMLElement, parentId: number | null = null): MenuItem | null => {
    const itemId = parseInt(element.dataset.itemId || '0')
    if (!itemId) return null

    const item = itemMap.get(itemId)
    if (!item) return null

    // Process children
    const childrenContainer = element.querySelector<HTMLElement>('.menu-tree-children')
    if (childrenContainer) {
      const childElements = childrenContainer.querySelectorAll<HTMLElement>(':scope > .menu-tree-item')
      item.children = Array.from(childElements)
        .map(child => processNode(child, itemId))
        .filter((child): child is MenuItem => child !== null)
    } else {
      item.children = []
    }

    return item
  }

  // Process all root items
  rootItems.forEach(element => {
    const item = processNode(element)
    if (item) {
      result.push(item)
    }
  })

  return result.length > 0 ? result : props.modelValue
}

watch(() => props.modelValue, () => {
  // Re-initialize drag-drop when items change
  // This ensures new items are draggable
}, { deep: true })
</script>

<style scoped>
.menu-tree-view {
  @apply min-h-[100px];
}
</style>
