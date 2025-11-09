<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, router } from '@inertiajs/vue3'

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
  created_at: string
  updated_at: string
}

interface Props {
  blocks: Block[]
  categories: Record<string, string>
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Admin Dashboard',
    href: '/admin/dashboard',
  },
  {
    title: 'Pages',
    href: '/admin/page-builder',
  },
  {
    title: 'Blocks',
    href: '/admin/page-builder/blocks',
  },
]

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const getCategoryName = (category: string) => {
  return props.categories[category] || category
}

const getStatusColor = (isActive: boolean) => {
  return isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
}

const editBlock = (block: Block) => {
  router.visit(`/admin/page-builder/blocks/${block.id}/edit`)
}

const toggleBlockStatus = async (block: Block) => {
  try {
    const response = await fetch(`/admin/page-builder/api/blocks/${block.id}`, {
      method: 'PUT',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        ...block,
        is_active: !block.is_active
      })
    })

    if (response.ok) {
      router.reload()
    } else {
      alert('Failed to update block status')
    }
  } catch (error) {
    console.error('Error updating block:', error)
    alert('Failed to update block status')
  }
}

const deleteBlock = async (block: Block) => {
  if (confirm(`Are you sure you want to delete "${block.name}"?`)) {
    try {
      const response = await fetch(`/admin/page-builder/api/blocks/${block.id}`, {
        method: 'DELETE',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      })

      if (response.ok) {
        router.reload()
      } else {
        alert('Failed to delete block')
      }
    } catch (error) {
      console.error('Error deleting block:', error)
      alert('Failed to delete block')
    }
  }
}
</script>

<template>
  <Head title="Blocks - Pages" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Page header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Blocks</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Manage reusable content blocks for the Pages
          </p>
        </div>
        <Link
          href="/admin/page-builder/blocks/create"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Create Block
        </Link>
      </div>

      <!-- Blocks grid -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div v-if="blocks.length === 0" class="p-6 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No blocks</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Get started by creating your first block.
          </p>
          <div class="mt-6">
            <Link
              href="/admin/page-builder/blocks/create"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Create Block
            </Link>
          </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
          <div
            v-for="block in blocks"
            :key="block.id"
            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-shadow"
          >
            <!-- Block preview -->
            <div class="aspect-video bg-gray-100 dark:bg-gray-700 rounded-t-lg flex items-center justify-center">
              <div v-if="block.preview_image" class="w-full h-full">
                <img :src="block.preview_image" :alt="block.name" class="w-full h-full object-cover rounded-t-lg" />
              </div>
              <div v-else class="text-gray-400 dark:text-gray-500">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
              </div>
            </div>

            <!-- Block info -->
            <div class="p-4">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ block.name }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ block.type }}</p>
                  <div class="mt-2 flex items-center space-x-2">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                      {{ getCategoryName(block.category) }}
                    </span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusColor(block.is_active)">
                      {{ block.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                  <p v-if="block.description" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ block.description }}
                  </p>
                </div>
              </div>

              <!-- Actions -->
              <div class="mt-4 flex items-center justify-between">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  Updated {{ formatDate(block.updated_at) }}
                </span>
                <div class="flex items-center space-x-2">
                  <button
                    @click="toggleBlockStatus(block)"
                    class="text-sm text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                  >
                    {{ block.is_active ? 'Deactivate' : 'Activate' }}
                  </button>
                  <button
                    @click="editBlock(block)"
                    class="text-sm text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteBlock(block)"
                    class="text-sm text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
