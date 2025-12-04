<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PostCard from './components/PostCard.vue'

interface SocialAccount {
  id: number
  platform: string
  platform_username: string
}

interface Post {
  id: number
  content: string
  status: string
  source_type: string
  scheduled_at: string | null
  published_at: string | null
  platform_permalink: string | null
  error_message: string | null
  retry_count: number
  account: SocialAccount
  created_at: string
}

interface PaginatedPosts {
  data: Post[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

interface Props {
  posts: PaginatedPosts
  accounts: SocialAccount[]
  filters: {
    status?: string
    platform?: string
    source_type?: string
  }
  breadcrumbs: Array<{ label: string; href?: string }>
}

const props = defineProps<Props>()

const localFilters = ref({
  status: props.filters.status || '',
  platform: props.filters.platform || '',
  source_type: props.filters.source_type || '',
})

const statuses = [
  { value: '', label: 'All Statuses' },
  { value: 'draft', label: 'Draft' },
  { value: 'scheduled', label: 'Scheduled' },
  { value: 'published', label: 'Published' },
  { value: 'failed', label: 'Failed' },
]

const platforms = computed(() => [
  { value: '', label: 'All Platforms' },
  ...Array.from(new Set(props.accounts.map((a) => a.platform))).map((platform) => ({
    value: platform,
    label: platform.charAt(0).toUpperCase() + platform.slice(1),
  })),
])

const sourceTypes = [
  { value: '', label: 'All Sources' },
  { value: 'manual', label: 'Manual' },
  { value: 'auto_blog_post', label: 'Auto (Blog Post)' },
]

const applyFilters = () => {
  router.get(
    '/admin/social-media/posts',
    {
      status: localFilters.value.status || undefined,
      platform: localFilters.value.platform || undefined,
      source_type: localFilters.value.source_type || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  )
}

const clearFilters = () => {
  localFilters.value = {
    status: '',
    platform: '',
    source_type: '',
  }
  applyFilters()
}

const hasActiveFilters = computed(() => {
  return localFilters.value.status || localFilters.value.platform || localFilters.value.source_type
})
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Social Media Posts" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Social Media Posts</h1>
          <p class="mt-2 text-sm text-neutral-600">
            Create, schedule, and manage posts across all connected platforms.
          </p>
        </div>
        <a
          href="/admin/social-media/posts/create"
          class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Post
        </a>
      </div>

      <!-- Filters -->
      <div class="rounded-lg border border-neutral-200 bg-white p-4">
        <div class="grid gap-4 md:grid-cols-4">
          <div>
            <label class="block text-sm font-medium text-neutral-700">Status</label>
            <select
              v-model="localFilters.status"
              class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              @change="applyFilters"
            >
              <option v-for="status in statuses" :key="status.value" :value="status.value">
                {{ status.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700">Platform</label>
            <select
              v-model="localFilters.platform"
              class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              @change="applyFilters"
            >
              <option v-for="platform in platforms" :key="platform.value" :value="platform.value">
                {{ platform.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700">Source</label>
            <select
              v-model="localFilters.source_type"
              class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              @change="applyFilters"
            >
              <option v-for="source in sourceTypes" :key="source.value" :value="source.value">
                {{ source.label }}
              </option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              v-if="hasActiveFilters"
              type="button"
              class="w-full rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 shadow-sm hover:bg-neutral-50"
              @click="clearFilters"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Posts Grid -->
      <div v-if="posts.data.length > 0" class="space-y-4">
        <PostCard v-for="post in posts.data" :key="post.id" :post="post" />
      </div>

      <!-- Empty State -->
      <div
        v-else
        class="rounded-lg border-2 border-dashed border-neutral-300 bg-neutral-50 p-12 text-center"
      >
        <svg
          class="mx-auto h-12 w-12 text-neutral-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
          />
        </svg>
        <h3 class="mt-2 text-sm font-semibold text-neutral-900">No posts found</h3>
        <p class="mt-1 text-sm text-neutral-500">
          {{ hasActiveFilters ? 'Try adjusting your filters.' : 'Get started by creating your first post.' }}
        </p>
        <div class="mt-6">
          <a
            v-if="!hasActiveFilters"
            href="/admin/social-media/posts/create"
            class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Post
          </a>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="posts.last_page > 1" class="flex items-center justify-between border-t border-neutral-200 bg-white px-4 py-3 sm:px-6">
        <div class="flex flex-1 justify-between sm:hidden">
          <a
            v-if="posts.current_page > 1"
            :href="`/admin/social-media/posts?page=${posts.current_page - 1}`"
            class="relative inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50"
          >
            Previous
          </a>
          <a
            v-if="posts.current_page < posts.last_page"
            :href="`/admin/social-media/posts?page=${posts.current_page + 1}`"
            class="relative ml-3 inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50"
          >
            Next
          </a>
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-neutral-700">
              Showing
              <span class="font-medium">{{ (posts.current_page - 1) * posts.per_page + 1 }}</span>
              to
              <span class="font-medium">{{ Math.min(posts.current_page * posts.per_page, posts.total) }}</span>
              of
              <span class="font-medium">{{ posts.total }}</span>
              results
            </p>
          </div>
          <div>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
              <a
                v-if="posts.current_page > 1"
                :href="`/admin/social-media/posts?page=${posts.current_page - 1}`"
                class="relative inline-flex items-center rounded-l-md px-2 py-2 text-neutral-400 ring-1 ring-inset ring-neutral-300 hover:bg-neutral-50"
              >
                <span class="sr-only">Previous</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                </svg>
              </a>
              <a
                v-for="page in posts.last_page"
                :key="page"
                :href="`/admin/social-media/posts?page=${page}`"
                :class="[
                  page === posts.current_page
                    ? 'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600'
                    : 'text-neutral-900 ring-1 ring-inset ring-neutral-300 hover:bg-neutral-50',
                  'relative inline-flex items-center px-4 py-2 text-sm font-semibold'
                ]"
              >
                {{ page }}
              </a>
              <a
                v-if="posts.current_page < posts.last_page"
                :href="`/admin/social-media/posts?page=${posts.current_page + 1}`"
                class="relative inline-flex items-center rounded-r-md px-2 py-2 text-neutral-400 ring-1 ring-inset ring-neutral-300 hover:bg-neutral-50"
              >
                <span class="sr-only">Next</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                </svg>
              </a>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
