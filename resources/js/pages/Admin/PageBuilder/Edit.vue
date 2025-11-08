<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { ref, reactive } from 'vue'
import PageBuilderCanvas from '@/components/pagebuilder/PageBuilderCanvas.vue'
import BlockConfigForm from '@/components/pagebuilder/BlockConfigForm.vue'
import PageRevisions from '@/components/pagebuilder/PageRevisions.vue'

interface Page {
  id: number
  title: string
  slug: string
  layout_id?: number
  data?: any
  status: 'draft' | 'published' | 'archived'
  meta_title?: string
  meta_description?: string
  meta_keywords?: string
  schema_data?: {
    type?: string
    headline?: string
    author?: string
    publisher?: string
    datePublished?: string
    brand?: string
    price?: string
  }
  layout?: {
    id: number
    name: string
  }
  author?: {
    id: number
    name: string
  }
}

interface Layout {
  id: number
  name: string
  slug: string
}

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
  page: Page
  layouts: Layout[]
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
    title: 'PageBuilder',
    href: '/admin/page-builder',
  },
  {
    title: 'Edit Page',
    href: `/admin/page-builder/${props.page.id}/edit`,
  },
]

const saving = ref(false)
const activeTab = ref('builder')

const form = reactive({
  title: props.page.title,
  slug: props.page.slug,
  layout_id: props.page.layout_id || '',
  meta_title: props.page.meta_title || '',
  meta_description: props.page.meta_description || '',
  meta_keywords: props.page.meta_keywords || '',
  data: props.page.data || { sections: [] },
  schema_data: props.page.schema_data || {
    type: '',
    headline: '',
    author: '',
    publisher: '',
    datePublished: '',
    brand: '',
    price: ''
  }
})

const savePage = async () => {
  try {
    saving.value = true

    const response = await fetch(`/admin/page-builder/api/pages/${props.page.id}`, {
      method: 'PUT',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(form)
    })

    if (response.ok) {
      // Success - could show a toast notification here
      console.log('Page saved successfully')
    } else {
      const errors = await response.json()
      console.error('Failed to save page:', errors)
      alert('Failed to save page. Please try again.')
    }
  } catch (error) {
    console.error('Error saving page:', error)
    alert('Failed to save page. Please try again.')
  } finally {
    saving.value = false
  }
}

const publishPage = async () => {
  try {
    await savePage()

    const response = await fetch(`/admin/page-builder/api/pages/${props.page.id}/publish`, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      router.reload()
    } else {
      alert('Failed to publish page')
    }
  } catch (error) {
    console.error('Error publishing page:', error)
    alert('Failed to publish page')
  }
}

const previewPage = async () => {
  try {
    const response = await fetch(`/admin/page-builder/api/pages/${props.page.id}/preview`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      const previewWindow = window.open('', '_blank')
      previewWindow?.document.write(data.html)
      previewWindow?.document.close()
    } else {
      alert('Failed to generate preview')
    }
  } catch (error) {
    console.error('Error generating preview:', error)
    alert('Failed to generate preview')
  }
}

interface SelectedBlockData {
  block: Block
  section: {
    id: string
    type: string
    config: Record<string, any>
  }
  index: number
}

const selectedBlock = ref<SelectedBlockData | null>(null)

const handleBlockSelected = (blockData: SelectedBlockData | null) => {
  selectedBlock.value = blockData
}

const closeBlockSettings = () => {
  selectedBlock.value = null
}

const handleConfigUpdate = (config) => {
  if (selectedBlock.value && selectedBlock.value.section) {
    // Find the actual section in the array and update it
    const sectionIndex = form.data.sections.findIndex(s => s.id === selectedBlock.value.section.id)
    if (sectionIndex !== -1) {
      form.data.sections[sectionIndex].config = { ...config }
    }
  }
}
</script>

<template>
  <Head :title="`Edit ${page.title} - PageBuilder`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Page header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Page</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ page.title }}
          </p>
        </div>
        <div class="flex gap-2">
          <button
            @click="previewPage"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            Preview
          </button>
          <button
            v-if="page.status === 'draft'"
            @click="publishPage"
            :disabled="saving"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
            Publish
          </button>
          <button
            @click="savePage"
            :disabled="saving"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ saving ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'builder'"
            :class="[
              activeTab === 'builder'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            Page Builder
          </button>
          <button
            @click="activeTab = 'settings'"
            :class="[
              activeTab === 'settings'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            Settings
          </button>
          <button
            @click="activeTab = 'revisions'"
            :class="[
              activeTab === 'revisions'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            Revisions
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div v-show="activeTab === 'builder'" class="space-y-6">
        <div class="grid gap-6" :class="selectedBlock ? 'grid-cols-12' : 'grid-cols-1'">
          <!-- Page Builder Canvas -->
          <div :class="selectedBlock ? 'col-span-8' : 'col-span-12'">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
              <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Page Builder</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Build your page using blocks</p>
              </div>
              <div class="p-6">
                <PageBuilderCanvas
                  v-model="form.data.sections"
                  :available-blocks="blocks"
                  :categories="categories"
                  @block-selected="handleBlockSelected"
                />
              </div>
            </div>
          </div>

          <!-- Block Settings Sidebar (shows only when a block is selected) -->
          <div v-if="selectedBlock" class="col-span-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow sticky top-6">
              <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between">
                <div>
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white">Block Settings</h3>
                  <p class="text-xs text-gray-500 mt-1">{{ selectedBlock.block?.name }}</p>
                </div>
                <button
                  @click="closeBlockSettings"
                  class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 ml-4"
                  title="Close settings"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
              <div class="p-4">
                <BlockConfigForm
                  v-if="selectedBlock.block && selectedBlock.section"
                  :block="selectedBlock.block"
                  :config="selectedBlock.section.config"
                  @update:config="handleConfigUpdate"
                />
                <div v-else class="text-red-600">
                  Error: Missing block or section data
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Settings Tab -->
      <div v-show="activeTab === 'settings'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Settings Column -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Page Settings -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
              <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Page Information</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Basic information about your page</p>
              </div>
              <div class="p-6 space-y-5">
                <div>
                  <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Title <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="title"
                    v-model="form.title"
                    type="text"
                    required
                    class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="Enter page title"
                  />
                  <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">The main title of your page</p>
                </div>

                <div>
                  <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    URL Slug <span class="text-red-500">*</span>
                  </label>
                  <div class="flex">
                    <span class="inline-flex items-center px-3 text-sm text-gray-500 bg-gray-50 dark:bg-gray-700 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg">
                      /
                    </span>
                    <input
                      id="slug"
                      v-model="form.slug"
                      type="text"
                      required
                      class="flex-1 px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                      placeholder="page-slug"
                    />
                  </div>
                  <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">URL-friendly version of the title (lowercase, hyphens only)</p>
                </div>

                <div>
                  <label for="layout" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Layout Template
                  </label>
                  <select
                    id="layout"
                    v-model="form.layout_id"
                    class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  >
                    <option value="">Default (No Layout)</option>
                    <option
                      v-for="layout in layouts"
                      :key="layout.id"
                      :value="layout.id"
                    >
                      {{ layout.name }}
                    </option>
                  </select>
                  <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Choose a layout template for this page</p>
                </div>
              </div>
            </div>

            <!-- SEO Settings -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
              <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">SEO & Meta Tags</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Optimize your page for search engines</p>
              </div>
              <div class="p-6 space-y-5">
                <div>
                  <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Meta Title
                  </label>
                  <input
                    id="meta_title"
                    v-model="form.meta_title"
                    type="text"
                    maxlength="60"
                    class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="SEO-optimized title (50-60 characters)"
                  />
                  <div class="flex items-center justify-between mt-1.5">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Recommended: 50-60 characters</p>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ (form.meta_title || '').length }}/60</span>
                  </div>
                </div>

                <div>
                  <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Meta Description
                  </label>
                  <textarea
                    id="meta_description"
                    v-model="form.meta_description"
                    rows="3"
                    maxlength="160"
                    class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                    placeholder="Brief description for search results (150-160 characters)"
                  ></textarea>
                  <div class="flex items-center justify-between mt-1.5">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Recommended: 150-160 characters</p>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ (form.meta_description || '').length }}/160</span>
                  </div>
                </div>

                <div>
                  <label for="meta_keywords" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Meta Keywords
                  </label>
                  <input
                    id="meta_keywords"
                    v-model="form.meta_keywords"
                    type="text"
                    class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="keyword1, keyword2, keyword3"
                  />
                  <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Comma-separated keywords (optional, mostly for legacy systems)</p>
                </div>
              </div>
            </div>

            <!-- Schema.org Configuration -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
              <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Schema.org Structured Data</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Help search engines better understand your content</p>
              </div>
              <div class="p-6 space-y-5">
                <div>
                  <label for="schema_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Schema Type
                  </label>
                  <select
                    id="schema_type"
                    v-model="form.schema_data.type"
                    class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  >
                    <option value="">None</option>
                    <option value="Article">Article</option>
                    <option value="BlogPosting">Blog Post</option>
                    <option value="NewsArticle">News Article</option>
                    <option value="WebPage">Web Page</option>
                    <option value="FAQPage">FAQ Page</option>
                    <option value="Product">Product</option>
                    <option value="Event">Event</option>
                    <option value="Organization">Organization</option>
                    <option value="Person">Person</option>
                  </select>
                  <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Select the most appropriate schema type for this page</p>
                </div>

                <!-- Article-specific fields -->
                <div v-if="form.schema_data.type && ['Article', 'BlogPosting', 'NewsArticle'].includes(form.schema_data.type)" class="space-y-5 pt-4 border-t border-gray-200 dark:border-gray-700">
                  <div>
                    <label for="schema_headline" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                      Headline
                    </label>
                    <input
                      id="schema_headline"
                      v-model="form.schema_data.headline"
                      type="text"
                      class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                      placeholder="Article headline"
                    />
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label for="schema_author" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Author Name
                      </label>
                      <input
                        id="schema_author"
                        v-model="form.schema_data.author"
                        type="text"
                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Author name"
                      />
                    </div>

                    <div>
                      <label for="schema_publisher" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Publisher
                      </label>
                      <input
                        id="schema_publisher"
                        v-model="form.schema_data.publisher"
                        type="text"
                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Publisher name"
                      />
                    </div>
                  </div>

                  <div>
                    <label for="schema_date_published" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                      Date Published
                    </label>
                    <input
                      id="schema_date_published"
                      v-model="form.schema_data.datePublished"
                      type="date"
                      class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    />
                  </div>
                </div>

                <!-- Product-specific fields -->
                <div v-if="form.schema_data.type === 'Product'" class="space-y-5 pt-4 border-t border-gray-200 dark:border-gray-700">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label for="schema_brand" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Brand
                      </label>
                      <input
                        id="schema_brand"
                        v-model="form.schema_data.brand"
                        type="text"
                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Brand name"
                      />
                    </div>

                    <div>
                      <label for="schema_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Price
                      </label>
                      <input
                        id="schema_price"
                        v-model="form.schema_data.price"
                        type="number"
                        step="0.01"
                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="0.00"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="lg:col-span-1 space-y-6">
            <!-- Page Status -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
              <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Page Status</h4>
              <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-600 dark:text-gray-400">Status:</span>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="{
                      'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': page.status === 'draft',
                      'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': page.status === 'published',
                      'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200': page.status === 'archived'
                    }">
                    {{ page.status.charAt(0).toUpperCase() + page.status.slice(1) }}
                  </span>
                </div>
                <div v-if="page.author" class="flex items-center justify-between text-sm">
                  <span class="text-gray-600 dark:text-gray-400">Author:</span>
                  <span class="text-gray-900 dark:text-white">{{ page.author.name }}</span>
                </div>
                <div v-if="page.layout" class="flex items-center justify-between text-sm">
                  <span class="text-gray-600 dark:text-gray-400">Layout:</span>
                  <span class="text-gray-900 dark:text-white">{{ page.layout.name }}</span>
                </div>
              </div>
            </div>

            <!-- Quick Tips -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
              <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-3 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                SEO Tips
              </h4>
              <ul class="space-y-2 text-xs text-blue-800 dark:text-blue-300">
                <li class="flex items-start">
                  <svg class="w-3 h-3 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  <span>Keep meta titles under 60 characters</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-3 h-3 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  <span>Write compelling meta descriptions (150-160 chars)</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-3 h-3 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  <span>Use Schema.org for rich search results</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-3 h-3 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  <span>URL slugs should be lowercase with hyphens</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Revisions Tab -->
      <div v-show="activeTab === 'revisions'" class="space-y-6">
        <div class="max-w-4xl">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Page Revisions</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View and restore previous versions of this page</p>
            </div>
            <div class="p-6">
              <PageRevisions :page-id="page.id" :available-blocks="blocks" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
