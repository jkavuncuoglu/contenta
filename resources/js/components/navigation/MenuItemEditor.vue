<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="$emit('close')"></div>

      <!-- Center modal -->
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

      <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Edit Menu Item
            </h3>
            <button
              @click="$emit('close')"
              class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Body -->
        <form @submit.prevent="save" class="px-6 py-4 space-y-4">
          <!-- Title -->
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Title <span class="text-red-500">*</span>
            </label>
            <input
              id="title"
              v-model="form.title"
              type="text"
              required
              class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- URL -->
          <div>
            <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              URL
            </label>
            <input
              id="url"
              v-model="form.url"
              type="text"
              class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
              placeholder="/page/about"
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Leave empty for linked items (pages, posts, etc.)
            </p>
          </div>

          <!-- Target -->
          <div>
            <label for="target" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Open Link In
            </label>
            <select
              id="target"
              v-model="form.target"
              class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
            >
              <option value="_self">Same Tab</option>
              <option value="_blank">New Tab</option>
              <option value="_parent">Parent Frame</option>
              <option value="_top">Full Window</option>
            </select>
          </div>

          <!-- CSS Classes -->
          <div>
            <label for="css_classes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              CSS Classes
            </label>
            <input
              id="css_classes"
              v-model="form.css_classes"
              type="text"
              class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
              placeholder="btn btn-primary"
            />
          </div>

          <!-- Icon -->
          <div>
            <label for="icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Icon (HTML or Class)
            </label>
            <input
              id="icon"
              v-model="form.icon"
              type="text"
              class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
              placeholder="<svg>...</svg> or iconify:icon-name"
            />
          </div>

          <!-- Visibility -->
          <div class="flex items-center">
            <input
              id="is_visible"
              v-model="form.is_visible"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="is_visible" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
              Visible (show this item in the menu)
            </label>
          </div>
        </form>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="save"
            :disabled="saving"
            class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ saving ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'

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
  menuId: number
}

interface Emits {
  (e: 'save', item: MenuItem): void
  (e: 'close'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const saving = ref(false)

const form = reactive({
  title: props.item.title,
  url: props.item.url || '',
  target: props.item.target,
  css_classes: props.item.css_classes || '',
  icon: props.item.icon || '',
  is_visible: props.item.is_visible,
})

const save = async () => {
  saving.value = true
  try {
    const response = await fetch(
      '/admin/menus/api/' + props.menuId + '/items/' + props.item.id,
      {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify(form),
      }
    )

    if (response.ok) {
      const updatedItem = await response.json()
      emit('save', updatedItem)
    } else {
      alert('Failed to save menu item')
    }
  } catch (error) {
    console.error('Failed to save menu item:', error)
    alert('Failed to save menu item')
  } finally {
    saving.value = false
  }
}
</script>
