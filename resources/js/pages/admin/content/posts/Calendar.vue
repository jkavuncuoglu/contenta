<template>
  <Head title="Content Calendar" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Page header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Content Calendar</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            View and manage your content schedule
          </p>
        </div>
        <div class="flex items-center gap-4">
          <Link
            href="/admin/posts"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <Icon name="list-bullet" class="w-4 h-4 mr-2" />
            List View
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

      <!-- Calendar controls -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <button
              @click="previousMonth"
              class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
              title="Previous month"
            >
              <Icon name="chevron-left" class="w-5 h-5" />
            </button>
            <button
              @click="nextMonth"
              class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
              title="Next month"
            >
              <Icon name="chevron-right" class="w-5 h-5" />
            </button>
            <button
              @click="goToToday"
              class="px-3 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700"
            >
              Today
            </button>
          </div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ currentMonthYear }}
          </h2>
          <div class="flex items-center gap-2">
            <div class="flex items-center gap-2 text-sm">
              <span class="inline-block w-3 h-3 rounded-full bg-green-500"></span>
              <span class="text-gray-600 dark:text-gray-400">Published</span>
              <span class="inline-block w-3 h-3 rounded-full bg-yellow-500 ml-4"></span>
              <span class="text-gray-600 dark:text-gray-400">Scheduled</span>
              <span class="inline-block w-3 h-3 rounded-full bg-gray-400 ml-4"></span>
              <span class="text-gray-600 dark:text-gray-400">Draft</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Calendar grid -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
          <div
            v-for="day in weekDays"
            :key="day"
            class="px-4 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide"
          >
            {{ day }}
          </div>
        </div>
        <div class="grid grid-cols-7 auto-rows-fr">
          <div
            v-for="(day, index) in calendarDays"
            :key="index"
            :class="[
              'min-h-32 border-r border-b border-gray-200 dark:border-gray-700 p-2',
              !day.isCurrentMonth && 'bg-gray-50 dark:bg-gray-900/50',
              day.isToday && 'bg-blue-50 dark:bg-blue-900/20'
            ]"
          >
            <div class="flex items-center justify-between mb-1">
              <span
                :class="[
                  'text-sm font-medium',
                  day.isCurrentMonth ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-600',
                  day.isToday && 'bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center'
                ]"
              >
                {{ day.date.getDate() }}
              </span>
            </div>
            <div class="space-y-1">
              <div
                v-for="post in getPostsForDay(day.date)"
                :key="post.id"
                @click="openPostDetails(post)"
                :class="[
                  'text-xs p-1 rounded cursor-pointer hover:shadow-md transition-shadow',
                  getPostClass(post.status)
                ]"
                :title="post.title"
              >
                <div class="font-medium truncate">{{ post.title }}</div>
                <div class="text-gray-600 dark:text-gray-400 text-[10px]">
                  {{ formatTime(post.published_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Scheduled Posts List -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Upcoming Scheduled Posts
        </h3>
        <div v-if="loading" class="text-center py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
        <div v-else-if="scheduledPosts.length === 0" class="text-center py-8">
          <Icon name="calendar" class="mx-auto h-12 w-12 text-gray-400" />
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            No scheduled posts
          </p>
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="post in scheduledPosts"
            :key="post.id"
            class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <div class="flex-1">
              <h4 class="font-medium text-gray-900 dark:text-white">{{ post.title }}</h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Scheduled for {{ formatDateTime(post.published_at) }}
              </p>
            </div>
            <div class="flex items-center gap-2">
              <Link
                :href="`/admin/posts/${post.id}/edit`"
                class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600"
                title="Edit post"
              >
                <Icon name="pencil" class="w-4 h-4" />
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Icon from '@/components/Icon.vue'
import { router } from '@inertiajs/vue3'

const breadcrumbs = [
  { title: 'Admin', href: '/admin' },
  { title: 'Content', href: '/admin/posts' },
  { title: 'Calendar', href: '/admin/posts/calendar' },
]

interface Post {
  id: number
  title: string
  slug: string
  status: 'published' | 'scheduled' | 'draft'
  published_at: string
  author: {
    id: number
    name: string
  }
  categories: Array<{
    id: number
    name: string
  }>
}

const currentDate = ref(new Date())
const posts = ref<Post[]>([])
const scheduledPosts = ref<Post[]>([])
const loading = ref(false)

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

const currentMonthYear = computed(() => {
  return currentDate.value.toLocaleDateString('en-US', {
    month: 'long',
    year: 'numeric'
  })
})

const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()

  const firstDayOfMonth = new Date(year, month, 1)
  const lastDayOfMonth = new Date(year, month + 1, 0)
  const firstDayOfWeek = firstDayOfMonth.getDay()
  const daysInMonth = lastDayOfMonth.getDate()

  const days = []
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  // Previous month days
  const prevMonthLastDay = new Date(year, month, 0).getDate()
  for (let i = firstDayOfWeek - 1; i >= 0; i--) {
    const date = new Date(year, month - 1, prevMonthLastDay - i)
    days.push({
      date,
      isCurrentMonth: false,
      isToday: false
    })
  }

  // Current month days
  for (let i = 1; i <= daysInMonth; i++) {
    const date = new Date(year, month, i)
    days.push({
      date,
      isCurrentMonth: true,
      isToday: date.getTime() === today.getTime()
    })
  }

  // Next month days
  const remainingDays = 42 - days.length
  for (let i = 1; i <= remainingDays; i++) {
    const date = new Date(year, month + 1, i)
    days.push({
      date,
      isCurrentMonth: false,
      isToday: false
    })
  }

  return days
})

function getPostsForDay(date: Date): Post[] {
  const dateStr = date.toISOString().split('T')[0]
  return posts.value.filter(post => {
    if (!post.published_at) return false
    const postDate = new Date(post.published_at).toISOString().split('T')[0]
    return postDate === dateStr
  })
}

function getPostClass(status: string): string {
  switch (status) {
    case 'published':
      return 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border-l-2 border-green-500'
    case 'scheduled':
      return 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 border-l-2 border-yellow-500'
    default:
      return 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border-l-2 border-gray-500'
  }
}

function formatTime(dateTime: string | null): string {
  if (!dateTime) return ''
  const date = new Date(dateTime)

  // Get formatted time
  const formattedTime = date.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })

  // Get timezone abbreviation
  const timeZoneString = date.toLocaleTimeString('en-US', {
    timeZoneName: 'short'
  })
  const tzAbbr = timeZoneString.split(' ').pop() || ''

  return `${formattedTime} ${tzAbbr}`
}

function formatDateTime(dateTime: string | null): string {
  if (!dateTime) return ''
  const date = new Date(dateTime)

  // Get formatted date and time
  const formattedDate = date.toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit'
  })

  // Get timezone abbreviation
  const timeZoneString = date.toLocaleTimeString('en-US', {
    timeZoneName: 'short'
  })
  const tzAbbr = timeZoneString.split(' ').pop() || ''

  return `${formattedDate} ${tzAbbr}`
}

function previousMonth() {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() - 1,
    1
  )
  fetchCalendarPosts()
}

function nextMonth() {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() + 1,
    1
  )
  fetchCalendarPosts()
}

function goToToday() {
  currentDate.value = new Date()
  fetchCalendarPosts()
}

function openPostDetails(post: Post) {
  router.visit(`/admin/posts/${post.id}/edit`)
}

async function fetchCalendarPosts() {
  loading.value = true

  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  const startDate = new Date(year, month, 1).toISOString().split('T')[0]
  const endDate = new Date(year, month + 1, 0).toISOString().split('T')[0]

  try {
    const response = await fetch(
      `/admin/api/posts/calendar?start_date=${startDate}&end_date=${endDate}`
    )
    const data = await response.json()
    posts.value = data.data || []
  } catch (error) {
    console.error('Failed to fetch calendar posts:', error)
  } finally {
    loading.value = false
  }
}

async function fetchScheduledPosts() {
  try {
    const response = await fetch('/admin/api/posts/scheduled?per_page=10')
    const data = await response.json()
    scheduledPosts.value = data.data || []
  } catch (error) {
    console.error('Failed to fetch scheduled posts:', error)
  }
}

onMounted(() => {
  fetchCalendarPosts()
  fetchScheduledPosts()
})
</script>
