<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { ref, reactive, watch, onMounted } from 'vue'

interface Block {
  id: number
  name: string
  type: string
  category: string
  config_schema: any
  component_path: string
  preview_image?: string
  description?: string
  is_active: boolean
}

interface Props {
  block: Block
  categories: Record<string, string>
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Admin Dashboard',
    href: '/admin/dashboard',
  },
  {
    title: 'PageBuilder',
    href: '/admin/page-builder',
  },
  {
    title: 'Blocks',
    href: '/admin/page-builder/blocks',
  },
  {
    title: 'Edit Block',
    href: `/admin/page-builder/blocks/${props.block.id}/edit`,
  },
]

const saving = ref(false)
const schemaFields = ref([])

const form = reactive({
  name: props.block.name,
  type: props.block.type,
  category: props.block.category,
  component_path: props.block.component_path,
  preview_image: props.block.preview_image || '',
  description: props.block.description || '',
  is_active: props.block.is_active,
})

// Convert schema to editable fields
onMounted(() => {
  const schema = props.block.config_schema || {}
  schemaFields.value = Object.entries(schema).map(([name, config]: [string, any]) => ({
    name,
    type: config.type || 'string',
    required: config.required || false,
    default: config.default || '',
    label: config.label || '',
    description: config.description || '',
    options: config.options || []
  }))

  // Ensure at least one field
  if (schemaFields.value.length === 0) {
    addSchemaField()
  }
})

const addSchemaField = () => {
  schemaFields.value.push({
    name: '',
    type: 'string',
    required: false,
    default: '',
    label: '',
    description: '',
    options: []
  })
}

const removeSchemaField = (index: number) => {
  schemaFields.value.splice(index, 1)
}

const addOption = (fieldIndex: number) => {
  if (!schemaFields.value[fieldIndex].options) {
    schemaFields.value[fieldIndex].options = []
  }
  schemaFields.value[fieldIndex].options.push('')
}

const removeOption = (fieldIndex: number, optionIndex: number) => {
  schemaFields.value[fieldIndex].options.splice(optionIndex, 1)
}

const updateBlock = async () => {
  try {
    saving.value = true

    // Build config schema from fields
    const configSchema = {}
    schemaFields.value.forEach(field => {
      if (field.name) {
        configSchema[field.name] = {
          type: field.type,
          required: field.required,
          default: field.default,
          label: field.label,
          description: field.description,
        }

        if (field.options && field.options.length > 0 && field.options.some(opt => opt.trim())) {
          configSchema[field.name].options = field.options.filter(opt => opt.trim())
        }
      }
    })

    const response = await fetch(`/admin/page-builder/api/blocks/${props.block.id}`, {
      method: 'PUT',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        ...form,
        config_schema: configSchema
      })
    })

    if (response.ok) {
      router.visit('/admin/page-builder/blocks')
    } else {
      const errors = await response.json()
      console.error('Failed to update block:', errors)
      alert('Failed to update block. Please check the form and try again.')
    }
  } catch (error) {
    console.error('Error updating block:', error)
    alert('Failed to update block. Please try again.')
  } finally {
    saving.value = false
  }
}

const goBack = () => {
  router.visit('/admin/page-builder/blocks')
}
</script>

<template>
  <Head :title="`Edit ${block.name} - PageBuilder`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Page header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Block</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ block.name }}
          </p>
        </div>
      </div>

      <!-- Form -->
      <div class="max-w-4xl">
        <form @submit.prevent="updateBlock" class="space-y-8">
          <!-- Basic Information -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Basic Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Block Name *
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="e.g., Hero Section"
                />
              </div>

              <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Block Type *
                </label>
                <input
                  id="type"
                  v-model="form.type"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="e.g., hero-block"
                />
              </div>

              <div>
                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Category *
                </label>
                <select
                  id="category"
                  v-model="form.category"
                  required
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option
                    v-for="(label, value) in categories"
                    :key="value"
                    :value="value"
                  >
                    {{ label }}
                  </option>
                </select>
              </div>

              <div>
                <label for="component_path" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Component Path *
                </label>
                <input
                  id="component_path"
                  v-model="form.component_path"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="e.g., hero-block"
                />
              </div>

              <div>
                <label for="preview_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Preview Image URL
                </label>
                <input
                  id="preview_image"
                  v-model="form.preview_image"
                  type="url"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="https://example.com/preview.jpg"
                />
              </div>

              <div class="flex items-center">
                <input
                  id="is_active"
                  v-model="form.is_active"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                  Active
                </label>
              </div>
            </div>

            <div class="mt-6">
              <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Description
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Describe what this block does..."
              ></textarea>
            </div>
          </div>

          <!-- Configuration Schema -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">Configuration Schema</h3>
              <button
                type="button"
                @click="addSchemaField"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Field
              </button>
            </div>

            <div class="space-y-6">
              <div
                v-for="(field, index) in schemaFields"
                :key="index"
                class="border border-gray-200 dark:border-gray-600 rounded-lg p-4"
              >
                <div class="flex items-center justify-between mb-4">
                  <h4 class="font-medium text-gray-900 dark:text-white">Field {{ index + 1 }}</h4>
                  <button
                    v-if="schemaFields.length > 1"
                    type="button"
                    @click="removeSchemaField(index)"
                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Field Name *
                    </label>
                    <input
                      v-model="field.name"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="e.g., title"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Type
                    </label>
                    <select
                      v-model="field.type"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="string">String</option>
                      <option value="number">Number</option>
                      <option value="boolean">Boolean</option>
                      <option value="array">Array</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Default Value
                    </label>
                    <input
                      v-model="field.default"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Default value"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Label
                    </label>
                    <input
                      v-model="field.label"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Display label"
                    />
                  </div>

                  <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Description
                    </label>
                    <input
                      v-model="field.description"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Field description"
                    />
                  </div>
                </div>

                <div class="mt-4 flex items-center">
                  <input
                    v-model="field.required"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Required field
                  </label>
                </div>

                <!-- Options for select fields -->
                <div v-if="field.type === 'string'" class="mt-4">
                  <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Options (for select fields)
                    </label>
                    <button
                      type="button"
                      @click="addOption(index)"
                      class="text-sm text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                      Add Option
                    </button>
                  </div>
                  <div v-if="field.options && field.options.length > 0" class="space-y-2">
                    <div
                      v-for="(option, optionIndex) in field.options"
                      :key="optionIndex"
                      class="flex items-center space-x-2"
                    >
                      <input
                        v-model="field.options[optionIndex]"
                        type="text"
                        class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Option value"
                      />
                      <button
                        type="button"
                        @click="removeOption(index, optionIndex)"
                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="goBack"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="!form.name || !form.type || saving"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'Saving...' : 'Update Block' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>