<template>
  <Head title="Posts" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Page header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Posts</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Manage your blog posts and articles
          </p>
        </div>
        <div class="flex items-center gap-3">
          <Link
            href="/admin/posts/calendar"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <Icon name="calendar" class="w-4 h-4 mr-2" />
            Calendar
          </Link>
          <Link
            href="/admin/posts/create"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <Icon name="plus" class="w-4 h-4 mr-2" />
            New Post
          </Link>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'all'"
            :class="[
              activeTab === 'all'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            All Posts
          </button>
          <button
            @click="activeTab = 'archived'"
            :class="[
              activeTab === 'archived'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            Archived
            <span v-if="archivedCount > 0" class="ml-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded-full text-xs">
              {{ archivedCount }}
            </span>
          </button>
        </nav>
      </div>

      <!-- All Posts Tab -->
      <div v-if="activeTab === 'all'" class="space-y-4">
        <!-- View controls and filters -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-800 shadow rounded-lg p-4">
          <div class="flex items-center gap-4">
            <!-- View Toggle -->
            <div class="flex items-center gap-2 border border-gray-300 dark:border-gray-600 rounded-md p-1">
              <button
                @click="viewMode = 'list'"
                :class="[
                  viewMode === 'list'
                    ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300',
                  'px-3 py-1.5 rounded text-sm font-medium transition-colors'
                ]"
                title="List view"
              >
                <Icon name="list-bullet" class="w-4 h-4" />
              </button>
              <button
                @click="viewMode = 'kanban'"
                :class="[
                  viewMode === 'kanban'
                    ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300',
                  'px-3 py-1.5 rounded text-sm font-medium transition-colors'
                ]"
                title="Kanban view"
              >
                <Icon name="view-columns" class="w-4 h-4" />
              </button>
            </div>

            <!-- Status Filter (for list view) -->
            <select
              v-if="viewMode === 'list'"
              v-model="statusFilter"
              @change="loadPosts"
              class="block border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm dark:bg-gray-700 dark:text-gray-300"
            >
              <option value="">All Statuses</option>
              <option value="draft">Draft</option>
              <option value="scheduled">Scheduled</option>
              <option value="published">Published</option>
            </select>
          </div>

          <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ filteredPosts.length }} post{{ filteredPosts.length !== 1 ? 's' : '' }}
          </div>
        </div>

        <!-- List View -->
        <div v-if="viewMode === 'list'" class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
          <div v-if="loading" class="p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
          <div v-else-if="filteredPosts.length === 0" class="p-6 text-center">
            <Icon name="document-text" class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No posts</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Get started by creating your first post.
            </p>
          </div>
          <table v-else class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Author</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="post in filteredPosts" :key="post.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4">
                  <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ post.title }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ post.excerpt || 'No excerpt' }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                  {{ post.author?.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(post.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ post.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ formatDate(post.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="`/admin/posts/${post.id}/edit`" class="text-blue-600 hover:text-blue-500 mr-4">
                    Edit
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Kanban View -->
        <div v-else-if="viewMode === 'kanban'" class="grid grid-cols-3 gap-6">
          <!-- Draft Column -->
          <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-gray-900 dark:text-white flex items-center">
                <span class="inline-block w-3 h-3 rounded-full bg-yellow-500 mr-2"></span>
                Draft
              </h3>
              <span class="text-sm text-gray-500 dark:text-gray-400">{{ draftPosts.length }}</span>
            </div>
            <div class="space-y-3 min-h-96">
              <div
                v-for="post in draftPosts"
                :key="post.id"
                draggable="true"
                @dragstart="handleDragStart(post, 'draft')"
                @dragend="handleDragEnd"
                @drop="handleDrop($event, 'draft')"
                @dragover.prevent
                class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow cursor-move hover:shadow-md transition-shadow border-l-4 border-yellow-500"
              >
                <h4 class="font-medium text-gray-900 dark:text-white mb-1">{{ post.title }}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2 line-clamp-2">{{ post.excerpt }}</p>
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                  <span>{{ post.author?.name }}</span>
                  <Link :href="`/admin/posts/${post.id}/edit`" class="text-blue-600 hover:text-blue-500">
                    Edit
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <!-- Scheduled Column -->
          <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-gray-900 dark:text-white flex items-center">
                <span class="inline-block w-3 h-3 rounded-full bg-blue-500 mr-2"></span>
                Scheduled
              </h3>
              <span class="text-sm text-gray-500 dark:text-gray-400">{{ scheduledPosts.length }}</span>
            </div>
            <div class="space-y-3 min-h-96">
              <div
                v-for="post in scheduledPosts"
                :key="post.id"
                draggable="true"
                @dragstart="handleDragStart(post, 'scheduled')"
                @dragend="handleDragEnd"
                @drop="handleDrop($event, 'scheduled')"
                @dragover.prevent
                class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow cursor-move hover:shadow-md transition-shadow border-l-4 border-blue-500"
              >
                <h4 class="font-medium text-gray-900 dark:text-white mb-1">{{ post.title }}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2 line-clamp-2">{{ post.excerpt }}</p>
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                  <span>{{ formatDate(post.published_at) }}</span>
                  <Link :href="`/admin/posts/${post.id}/edit`" class="text-blue-600 hover:text-blue-500">
                    Edit
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <!-- Published Column -->
          <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-gray-900 dark:text-white flex items-center">
                <span class="inline-block w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                Published
              </h3>
              <span class="text-sm text-gray-500 dark:text-gray-400">{{ publishedPosts.length }}</span>
            </div>
            <div class="space-y-3 min-h-96">
              <div
                v-for="post in publishedPosts"
                :key="post.id"
                draggable="true"
                @dragstart="handleDragStart(post, 'published')"
                @dragend="handleDragEnd"
                @drop="handleDrop($event, 'published')"
                @dragover.prevent
                class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow cursor-move hover:shadow-md transition-shadow border-l-4 border-green-500"
              >
                <h4 class="font-medium text-gray-900 dark:text-white mb-1">{{ post.title }}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2 line-clamp-2">{{ post.excerpt }}</p>
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                  <span>{{ formatDate(post.published_at) }}</span>
                  <Link :href="`/admin/posts/${post.id}/edit`" class="text-blue-600 hover:text-blue-500">
                    Edit
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Archived Tab -->
      <div v-else-if="activeTab === 'archived'" class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div v-if="loadingArchived" class="p-8 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
        <div v-else-if="archivedPosts.length === 0" class="p-6 text-center">
          <Icon name="archive-box" class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No archived posts</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Deleted posts will appear here.
          </p>
        </div>
        <table v-else class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Title</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Author</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Deleted</th>
              <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="post in archivedPosts" :key="post.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-6 py-4">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ post.title }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ post.excerpt || 'No excerpt' }}</p>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                {{ post.author?.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(post.deleted_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                <Link :href="`/admin/posts/${post.id}/edit`" class="text-blue-600 hover:text-blue-500">
                  Edit
                </Link>
                <button @click="restorePost(post.id)" class="text-green-600 hover:text-green-500">
                  Restore
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Icon from '@/components/Icon.vue'

const breadcrumbs = [
  { title: 'Posts', href: '/admin/posts' },
]

interface Post {
  id: number
  title: string
  excerpt?: string
  status: string
  created_at: string
  published_at?: string
  deleted_at?: string
  author?: {
    name: string
  }
}

const activeTab = ref<'all' | 'archived'>('all')
const viewMode = ref<'list' | 'kanban'>('list')
const statusFilter = ref('')
const posts = ref<Post[]>([])
const archivedPosts = ref<Post[]>([])
const loading = ref(false)
const loadingArchived = ref(false)
const draggedPost = ref<Post | null>(null)
const draggedFromColumn = ref<string | null>(null)

const filteredPosts = computed(() => {
  if (!statusFilter.value) return posts.value
  return posts.value.filter(post => post.status === statusFilter.value)
})

const draftPosts = computed(() => posts.value.filter(p => p.status === 'draft'))
const scheduledPosts = computed(() => posts.value.filter(p => p.status === 'scheduled'))
const publishedPosts = computed(() => posts.value.filter(p => p.status === 'published'))
const archivedCount = computed(() => archivedPosts.value.length)

async function loadPosts() {
  loading.value = true
  try {
    const url = statusFilter.value
      ? `/admin/api/posts?status=${statusFilter.value}&per_page=100`
      : '/admin/api/posts?per_page=100'
    const response = await fetch(url)
    const data = await response.json()
    posts.value = data.data || []
  } catch (error) {
    console.error('Failed to load posts:', error)
  } finally {
    loading.value = false
  }
}

async function loadArchivedPosts() {
  loadingArchived.value = true
  try {
    const response = await fetch('/admin/api/posts/archived?per_page=100')
    const data = await response.json()
    archivedPosts.value = data.data || []
  } catch (error) {
    console.error('Failed to load archived posts:', error)
  } finally {
    loadingArchived.value = false
  }
}

async function restorePost(postId: number) {
  if (!confirm('Restore this post to draft status?')) return

  try {
    const response = await fetch(`/admin/api/posts/${postId}/restore`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })

    if (response.ok) {
      archivedPosts.value = archivedPosts.value.filter(p => p.id !== postId)
      await loadPosts()
      router.reload({ only: ['posts'] })
    }
  } catch (error) {
    console.error('Failed to restore post:', error)
  }
}

async function changePostStatus(postId: number, newStatus: string) {
  try {
    const response = await fetch(`/admin/api/posts/${postId}/status`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({ status: newStatus }),
    })

    if (response.ok) {
      await loadPosts()
    }
  } catch (error) {
    console.error('Failed to change post status:', error)
  }
}

function handleDragStart(post: Post, column: string) {
  draggedPost.value = post
  draggedFromColumn.value = column
}

function handleDragEnd() {
  draggedPost.value = null
  draggedFromColumn.value = null
}

async function handleDrop(event: DragEvent, targetColumn: string) {
  event.preventDefault()

  if (!draggedPost.value || draggedFromColumn.value === targetColumn) {
    return
  }

  await changePostStatus(draggedPost.value.id, targetColumn)
}

function getStatusClass(status: string) {
  const classes: Record<string, string> = {
    published: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
    draft: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
    scheduled: 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
  }
  return classes[status] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
}

function formatDate(dateString: string | undefined) {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

onMounted(() => {
  loadPosts()
  loadArchivedPosts()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
