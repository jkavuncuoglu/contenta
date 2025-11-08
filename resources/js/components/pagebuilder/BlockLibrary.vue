<template>
  <div class="block-library">
    <!-- Search and Filter -->
    <div class="mb-4">
      <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search blocks..."
          class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm"
        />
        <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </div>
    </div>

    <!-- Category Filter -->
    <div class="mb-4">
      <select
        v-model="selectedCategory"
        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm"
      >
        <option value="">All Categories</option>
        <option
          v-for="(label, value) in categories"
          :key="value"
          :value="value"
        >
          {{ label }}
        </option>
      </select>
    </div>

    <!-- Blocks Grid -->
    <div class="space-y-6">
      <div
        v-for="(categoryBlocks, category) in groupedBlocks"
        :key="category"
        v-show="!selectedCategory || selectedCategory === category"
      >
        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">
          {{ getCategoryLabel(category) }}
        </h4>

        <div class="grid grid-cols-1 gap-3">
          <div
            v-for="block in categoryBlocks"
            :key="block.id"
            class="block-item group relative bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-3 cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-md transition-all"
            draggable="true"
            @dragstart="handleDragStart($event, block)"
            @dragend="handleDragEnd"
            @click="addBlock(block)"
          >
            <!-- Block Preview -->
            <div class="aspect-video bg-gray-100 dark:bg-gray-600 rounded-md mb-3 flex items-center justify-center overflow-hidden">
              <img
                v-if="block.preview_image"
                :src="block.preview_image"
                :alt="block.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="text-gray-400 dark:text-gray-500">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
              </div>
            </div>

            <!-- Block Info -->
            <div>
              <h5 class="text-sm font-medium text-gray-900 dark:text-white">{{ block.name }}</h5>
              <p v-if="block.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                {{ block.description }}
              </p>
            </div>

            <!-- Drag Indicator -->
            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
              </svg>
            </div>

            <!-- Add Button Overlay -->
            <div class="absolute inset-0 bg-blue-600 bg-opacity-0 hover:bg-opacity-10 rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
              <button class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="Object.keys(groupedBlocks).length === 0" class="text-center py-8">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No blocks found</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ searchQuery ? 'Try adjusting your search terms.' : 'No blocks available.' }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

interface Block {
  id: number
  name: string
  type: string
  category: string
  config_schema: any
  preview_image?: string
  description?: string
  is_active: boolean
}

interface Props {
  blocks: Block[]
  categories: Record<string, string>
}

interface Emits {
  (e: 'add-block', block: Block): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const searchQuery = ref('')
const selectedCategory = ref('')

const filteredBlocks = computed(() => {
  let filtered = props.blocks.filter(block => block.is_active)

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(block =>
      block.name.toLowerCase().includes(query) ||
      block.description?.toLowerCase().includes(query) ||
      block.type.toLowerCase().includes(query)
    )
  }

  // Filter by category
  if (selectedCategory.value) {
    filtered = filtered.filter(block => block.category === selectedCategory.value)
  }

  return filtered
})

const groupedBlocks = computed(() => {
  const groups: Record<string, Block[]> = {}

  filteredBlocks.value.forEach(block => {
    if (!groups[block.category]) {
      groups[block.category] = []
    }
    groups[block.category].push(block)
  })

  // Sort blocks within each category by name
  Object.keys(groups).forEach(category => {
    groups[category].sort((a, b) => a.name.localeCompare(b.name))
  })

  return groups
})

const getCategoryLabel = (category: string) => {
  return props.categories[category] || category
}

const handleDragStart = (event: DragEvent, block: Block) => {
  event.dataTransfer?.setData('text/block-type', block.type)
  event.dataTransfer?.setData('text/block-id', block.id.toString())

  // Create a drag image
  const dragImage = event.target as HTMLElement
  const clone = dragImage.cloneNode(true) as HTMLElement
  clone.style.transform = 'scale(0.8)'
  clone.style.opacity = '0.8'
  clone.style.backgroundColor = 'white'
  clone.style.border = '2px solid #3b82f6'
  clone.style.borderRadius = '8px'

  document.body.appendChild(clone)
  event.dataTransfer?.setDragImage(clone, 0, 0)

  // Clean up after a short delay
  setTimeout(() => {
    document.body.removeChild(clone)
  }, 0)
}

const handleDragEnd = () => {
  // Clean up any drag effects
}

const addBlock = (block: Block) => {
  emit('add-block', block)
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.block-item {
  transition: all 0.2s ease;
}

.block-item:hover {
  transform: translateY(-2px);
}
</style>