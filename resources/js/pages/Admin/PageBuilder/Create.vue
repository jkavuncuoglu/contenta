<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, reactive, watch, onMounted } from 'vue'

interface Layout {
  id: number
  name: string
  slug: string
}

interface Props {
  layouts: Layout[]
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
    title: 'Create Page',
    href: '/admin/page-builder/create',
  },
]

const creating = ref(false)

const form = reactive({
  title: '',
  slug: '',
  layout_id: '',
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
})

// Auto-generate slug from title
watch(() => form.title, (newTitle) => {
  if (newTitle && !form.slug) {
    form.slug = newTitle
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .trim()
  }
})

const createPage = async () => {
  try {
    creating.value = true

    // Use fetch for API call since we're dealing with JSON endpoints
    const response = await fetch('/admin/page-builder/api/pages', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(form)
    })

    if (response.ok) {
      const page = await response.json()
      router.visit(`/admin/page-builder/${page.id}/edit`)
    } else {
      const errors = await response.json()
      console.error('Failed to create page:', errors)
      alert('Failed to create page. Please check the form and try again.')
    }
  } catch (error) {
    console.error('Error creating page:', error)
    alert('Failed to create page. Please try again.')
  } finally {
    creating.value = false
  }
}

const goBack = () => {
  router.visit('/admin/page-builder')
}

onMounted(() => {
  // Set default layout if only one available
  if (props.layouts.length === 1) {
    form.layout_id = props.layouts[0].id.toString()
  }
})
</script>

<template>
  <Head title="Create Page - Pages" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Page header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create New Page</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Build a new page with the Pages
          </p>
        </div>
      </div>

      <!-- Form -->
      <div class="max-w-2xl">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <form @submit.prevent="createPage" class="space-y-6">
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Page Title *
              </label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter page title"
              />
            </div>

            <div>
              <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Page Slug
              </label>
              <input
                id="slug"
                v-model="form.slug"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="page-slug (auto-generated from title)"
              />
            </div>

            <div>
              <label for="layout" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Page Layout
              </label>
              <select
                id="layout"
                v-model="form.layout_id"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Select a layout</option>
                <option
                  v-for="layout in layouts"
                  :key="layout.id"
                  :value="layout.id"
                >
                  {{ layout.name }}
                </option>
              </select>
            </div>

            <div class="border-t dark:border-gray-600 pt-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">SEO Settings</h3>

              <div class="space-y-4">
                <div>
                  <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Meta Title
                  </label>
                  <input
                    id="meta_title"
                    v-model="form.meta_title"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="SEO title for search engines"
                  />
                </div>

                <div>
                  <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Meta Description
                  </label>
                  <textarea
                    id="meta_description"
                    v-model="form.meta_description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Brief description for search engines"
                  ></textarea>
                </div>

                <div>
                  <label for="meta_keywords" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Meta Keywords
                  </label>
                  <input
                    id="meta_keywords"
                    v-model="form.meta_keywords"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="keyword1, keyword2, keyword3"
                  />
                </div>
              </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t dark:border-gray-600">
              <button
                type="button"
                @click="goBack"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="!form.title || creating"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ creating ? 'Creating...' : 'Create Page' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
