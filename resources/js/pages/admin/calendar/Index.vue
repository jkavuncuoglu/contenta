<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ref, computed, onMounted, watch } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import MonthView from './components/MonthView.vue'
import ListView from './components/ListView.vue'
import { Icon } from '@iconify/vue'

interface Breadcrumb {
  label: string
  href?: string
}

interface Props {
  breadcrumbs: Breadcrumb[]
}

interface CalendarEvent {
  id: number
  type: 'blog' | 'social'
  platform?: string
  title: string
  content?: string
  status: string
  date: string
  author?: string
  account?: string
  url: string
  color: string
}

const props = defineProps<Props>()

const viewMode = ref<'month' | 'list'>('month')
const currentDate = ref(new Date())
const events = ref<CalendarEvent[]>([])
const loading = ref(false)

const filters = ref({
  showBlog: true,
  showSocial: true,
  platform: '',
  status: '',
})

const platforms = [
  { value: '', label: 'All Platforms' },
  { value: 'twitter', label: 'Twitter/X' },
  { value: 'facebook', label: 'Facebook' },
  { value: 'linkedin', label: 'LinkedIn' },
  { value: 'instagram', label: 'Instagram' },
  { value: 'pinterest', label: 'Pinterest' },
  { value: 'tiktok', label: 'TikTok' },
]

const statuses = [
  { value: '', label: 'All Statuses' },
  { value: 'draft', label: 'Draft' },
  { value: 'scheduled', label: 'Scheduled' },
  { value: 'published', label: 'Published' },
  { value: 'failed', label: 'Failed' },
]

const currentViewComponent = computed(() => {
  return viewMode.value === 'month' ? MonthView : ListView
})

const startOfMonth = (date: Date) => {
  return new Date(date.getFullYear(), date.getMonth(), 1)
}

const endOfMonth = (date: Date) => {
  return new Date(date.getFullYear(), date.getMonth() + 1, 0, 23, 59, 59)
}

const formatMonthYear = (date: Date) => {
  return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
}

const previousMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
}

const nextMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
}

const goToToday = () => {
  currentDate.value = new Date()
}

async function fetchEvents() {
  loading.value = true
  try {
    const types = []
    if (filters.value.showBlog) types.push('blog')
    if (filters.value.showSocial) types.push('social')

    const params = new URLSearchParams({
      start_date: startOfMonth(currentDate.value).toISOString().split('T')[0],
      end_date: endOfMonth(currentDate.value).toISOString().split('T')[0],
      types: types.join(','),
    })

    if (filters.value.platform) {
      params.append('platform', filters.value.platform)
    }

    if (filters.value.status) {
      params.append('status', filters.value.status)
    }

    const response = await fetch(`/admin/api/calendar/data?${params}`)
    const data = await response.json()
    events.value = data.data
  } catch (error) {
    console.error('Error fetching calendar events:', error)
  } finally {
    loading.value = false
  }
}

watch([currentDate, filters], () => {
  fetchEvents()
}, { deep: true })

onMounted(() => {
  fetchEvents()
})
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Content Calendar" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Content Calendar</h1>
          <p class="mt-2 text-sm text-gray-600">
            View all your blog posts and social media posts in one unified calendar.
          </p>
        </div>

        <!-- View Mode Toggle -->
        <div class="flex gap-2">
          <button
            :class="[
              'rounded-md px-4 py-2 text-sm font-medium transition-colors',
              viewMode === 'month'
                ? 'bg-blue-600 text-white'
                : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
            ]"
            @click="viewMode = 'month'"
          >
            <Icon icon="material-symbols-light:calendar-month" class="inline h-5 w-5 mr-1" />
            Month
          </button>
          <button
            :class="[
              'rounded-md px-4 py-2 text-sm font-medium transition-colors',
              viewMode === 'list'
                ? 'bg-blue-600 text-white'
                : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
            ]"
            @click="viewMode = 'list'"
          >
            <Icon icon="material-symbols-light:list" class="inline h-5 w-5 mr-1" />
            List
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="rounded-lg border border-gray-200 bg-white p-4">
        <div class="grid gap-4 md:grid-cols-5">
          <!-- Content Type Filters -->
          <div class="flex flex-col gap-2">
            <label class="text-sm font-medium text-gray-700">Content Type</label>
            <label class="flex items-center gap-2">
              <input
                v-model="filters.showBlog"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="text-sm text-gray-700">Blog Posts</span>
            </label>
            <label class="flex items-center gap-2">
              <input
                v-model="filters.showSocial"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="text-sm text-gray-700">Social Posts</span>
            </label>
          </div>

          <!-- Platform Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Platform</label>
            <select
              v-model="filters.platform"
              :disabled="!filters.showSocial"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
            >
              <option v-for="platform in platforms" :key="platform.value" :value="platform.value">
                {{ platform.label }}
              </option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select
              v-model="filters.status"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            >
              <option v-for="status in statuses" :key="status.value" :value="status.value">
                {{ status.label }}
              </option>
            </select>
          </div>

          <!-- Navigation -->
          <div class="md:col-span-2 flex items-end gap-2">
            <button
              type="button"
              class="flex-1 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
              @click="previousMonth"
            >
              <Icon icon="material-symbols-light:chevron-left" class="inline h-5 w-5" />
              Previous
            </button>
            <button
              type="button"
              class="flex-1 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
              @click="goToToday"
            >
              Today
            </button>
            <button
              type="button"
              class="flex-1 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
              @click="nextMonth"
            >
              Next
              <Icon icon="material-symbols-light:chevron-right" class="inline h-5 w-5" />
            </button>
          </div>
        </div>
      </div>

      <!-- Current Month/Year -->
      <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900">{{ formatMonthYear(currentDate) }}</h2>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <Icon icon="mdi:loading" class="h-8 w-8 animate-spin text-blue-600" />
        <span class="ml-2 text-gray-600">Loading events...</span>
      </div>

      <!-- Calendar View -->
      <component
        v-else
        :is="currentViewComponent"
        :events="events"
        :current-date="currentDate"
      />

      <!-- Empty State -->
      <div
        v-if="!loading && events.length === 0"
        class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-12 text-center"
      >
        <Icon icon="material-symbols-light:calendar-month" class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-semibold text-gray-900">No events found</h3>
        <p class="mt-1 text-sm text-gray-500">
          Try adjusting your filters or selecting a different month.
        </p>
      </div>
    </div>
  </AppLayout>
</template>
