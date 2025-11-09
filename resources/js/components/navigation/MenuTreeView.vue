<template>
  <div class="menu-tree-view">
    <div v-if="modelValue.length > 0" ref="treeContainer" class="space-y-2">
      <MenuTreeNode
        v-for="(item, index) in modelValue"
        :key="item.id"
        :item="item"
        :level="0"
        :is-first="index === 0"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
        @toggle-visibility="toggleVisibility"
        @indent="handleIndent"
        @outdent="handleOutdent"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, toRef } from 'vue'
import { dragAndDrop } from '@formkit/drag-and-drop/vue'
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

// Handle indenting (move item to become child of previous sibling)
const handleIndent = (itemId: number) => {
  const indentItem = (items: MenuItem[], parentItems: MenuItem[] | null = null, index: number = -1): MenuItem[] => {
    for (let i = 0; i < items.length; i++) {
      const item = items[i]

      if (item.id === itemId && i > 0) {
        // Found the item and it has a previous sibling
        const previousSibling = items[i - 1]
        const itemToMove = { ...item }

        // Remove from current position
        const newItems = [...items]
        newItems.splice(i, 1)

        // Add as child of previous sibling
        const updatedPreviousSibling = {
          ...previousSibling,
          children: [...(previousSibling.children || []), itemToMove]
        }
        newItems[i - 1] = updatedPreviousSibling

        return newItems
      }

      // Recursively check children
      if (item.children && item.children.length > 0) {
        const newChildren = indentItem(item.children, items, i)
        if (newChildren !== item.children) {
          const newItems = [...items]
          newItems[i] = { ...item, children: newChildren }
          return newItems
        }
      }
    }

    return items
  }

  const updated = indentItem(props.modelValue)
  emit('update:modelValue', updated)
  saveReorder(updated)
}

// Handle outdenting (move item up one level)
const handleOutdent = (itemId: number) => {
  let foundItem: MenuItem | null = null
  let found = false

  const processLevel = (items: MenuItem[]): MenuItem[] => {
    const newItems: MenuItem[] = []

    for (let i = 0; i < items.length; i++) {
      const item = items[i]

      // Check if any of this item's children is the one to outdent
      if (!found && item.children && item.children.length > 0) {
        const childIndex = item.children.findIndex(child => child.id === itemId)

        if (childIndex !== -1) {
          // Found it! This child needs to be promoted to sibling
          found = true
          foundItem = { ...item.children[childIndex] }

          // Remove the child from this item's children
          const newChildren = [...item.children]
          newChildren.splice(childIndex, 1)

          // Add the updated parent (with child removed)
          newItems.push({ ...item, children: newChildren })

          // Add the promoted child as a sibling right after its parent
          newItems.push(foundItem)

          // Add remaining siblings
          for (let j = i + 1; j < items.length; j++) {
            newItems.push(items[j])
          }

          return newItems
        }

        // Not found in immediate children, check deeper
        const processedChildren = processLevel(item.children)
        if (found) {
          // Found somewhere deeper, just update this item's children
          newItems.push({ ...item, children: processedChildren })
        } else {
          newItems.push(item)
        }
      } else {
        newItems.push(item)
      }
    }

    return newItems
  }

  const updated = processLevel(props.modelValue)
  if (found) {
    emit('update:modelValue', updated)
    saveReorder(updated)
  }
}

// Initialize drag-and-drop
const initializeDragAndDrop = () => {
  nextTick(() => {
    if (!treeContainer.value || props.modelValue.length === 0) return

    try {
      dragAndDrop({
        parent: treeContainer.value,
        values: toRef(props, 'modelValue'),
        dragHandle: '.drag-handle',
        draggingClass: 'dragging',
        dropZoneClass: 'drop-zone-active',
        onSort: (data) => {
          // Update the model with new order
          emit('update:modelValue', data.values)
          saveReorder(data.values)
        },
      })
    } catch (error) {
      console.error('Failed to initialize drag-and-drop:', error)
    }
  })
}

// Save reorder to backend
const saveReorder = async (items: MenuItem[]) => {
  try {
    // Convert to the format expected by the backend (id and children only)
    const treeStructure = buildTreeStructure(items)

    await fetch(`/admin/menus/api/${props.menuId}/items/reorder`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({ items: treeStructure }),
    })

    emit('update', items)
  } catch (error) {
    console.error('Failed to save menu order:', error)
  }
}

// Build tree structure with only id and children for reorder API
const buildTreeStructure = (items: MenuItem[]): any[] => {
  return items.map(item => {
    const node: any = { id: item.id }

    if (item.children && item.children.length > 0) {
      node.children = buildTreeStructure(item.children)
    }

    return node
  })
}

onMounted(() => {
  initializeDragAndDrop()
})
</script>

<style scoped>
.menu-tree-view {
  min-height: 100px;
}

/* Dragging states */
:deep(.dragging) {
  opacity: 0.5;
}

:deep(.drop-zone-active) {
  background-color: rgb(239 246 255);
  border: 2px dashed rgb(59 130 246);
}

@media (prefers-color-scheme: dark) {
  :deep(.drop-zone-active) {
    background-color: rgb(30 58 138 / 0.2);
  }
}

/* Smooth transitions for drag operations */
:deep(.menu-tree-item) {
  transition: transform 0.2s ease, opacity 0.2s ease;
}
</style>
