<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

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

interface Props {
  events: CalendarEvent[]
  currentDate: Date
}

const props = defineProps<Props>()

const groupedEvents = computed(() => {
  const groups: Record<string, CalendarEvent[]> = {}

  props.events.forEach((event) => {
    const date = new Date(event.date)
    const dateKey = date.toISOString().split('T')[0]

    if (!groups[dateKey]) {
      groups[dateKey] = []
    }

    groups[dateKey].push(event)
  })

  return Object.entries(groups)
    .sort(([a], [b]) => a.localeCompare(b))
    .map(([date, events]) => ({
      date: new Date(date),
      events: events.sort((a, b) => a.date.localeCompare(b.date)),
    }))
})

const formatDate = (date: Date) => {
  return date.toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const formatTime = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
  })
}

const getEventIcon = (event: CalendarEvent) => {
  if (event.type === 'blog') {
    return 'material-symbols-light:article'
  }

  const platformIcons: Record<string, string> = {
    twitter: 'mdi:twitter',
    facebook: 'mdi:facebook',
    linkedin: 'mdi:linkedin',
    instagram: 'mdi:instagram',
    pinterest: 'mdi:pinterest',
    tiktok: 'simple-icons:tiktok',
  }

  return platformIcons[event.platform || ''] || 'material-symbols-light:share'
}

const getStatusBadge = (status: string) => {
  const badges: Record<string, { label: string; color: string }> = {
    draft: { label: 'Draft', color: 'bg-gray-100 text-gray-800' },
    scheduled: { label: 'Scheduled', color: 'bg-blue-100 text-blue-800' },
    published: { label: 'Published', color: 'bg-green-100 text-green-800' },
    failed: { label: 'Failed', color: 'bg-red-100 text-red-800' },
  }

  return badges[status] || { label: status, color: 'bg-gray-100 text-gray-800' }
}
</script>

<template>
  <div class="space-y-6">
    <div v-for="group in groupedEvents" :key="group.date.toISOString()" class="space-y-3">
      <!-- Date Header -->
      <div class="sticky top-0 z-10 border-b border-gray-200 bg-white py-2">
        <h3 class="text-lg font-semibold text-gray-900">{{ formatDate(group.date) }}</h3>
      </div>

      <!-- Events for this date -->
      <div class="space-y-2">
        <Link
          v-for="event in group.events"
          :key="`${event.type}-${event.id}`"
          :href="event.url"
          class="block rounded-lg border border-gray-200 bg-white p-4 hover:border-gray-300 hover:shadow-sm transition-all"
        >
          <div class="flex items-start gap-4">
            <!-- Time -->
            <div class="flex-shrink-0 text-sm text-gray-500">
              {{ formatTime(event.date) }}
            </div>

            <!-- Icon -->
            <div
              class="flex-shrink-0 flex h-10 w-10 items-center justify-center rounded-lg"
              :style="{ backgroundColor: event.color + '20' }"
            >
              <Icon
                :icon="getEventIcon(event)"
                class="h-5 w-5"
                :style="{ color: event.color }"
              />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <h4 class="text-sm font-semibold text-gray-900 truncate">{{ event.title }}</h4>
                <span
                  :class="[
                    'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                    getStatusBadge(event.status).color,
                  ]"
                >
                  {{ getStatusBadge(event.status).label }}
                </span>
              </div>

              <!-- Event Details -->
              <div class="flex items-center gap-4 text-xs text-gray-500">
                <span v-if="event.type === 'blog'" class="flex items-center gap-1">
                  <Icon icon="material-symbols-light:person" class="h-4 w-4" />
                  {{ event.author }}
                </span>
                <span v-if="event.type === 'social'" class="flex items-center gap-1">
                  <Icon icon="material-symbols-light:alternate-email" class="h-4 w-4" />
                  {{ event.account }}
                </span>
                <span v-if="event.type === 'social'" class="flex items-center gap-1">
                  <Icon :icon="getEventIcon(event)" class="h-4 w-4" />
                  {{ event.platform?.charAt(0).toUpperCase() + event.platform?.slice(1) }}
                </span>
              </div>

              <!-- Content Preview (for social posts) -->
              <p v-if="event.type === 'social' && event.content" class="mt-2 text-sm text-gray-600">
                {{ event.content }}
              </p>
            </div>

            <!-- Arrow -->
            <div class="flex-shrink-0">
              <Icon icon="material-symbols-light:chevron-right" class="h-5 w-5 text-gray-400" />
            </div>
          </div>
        </Link>
      </div>
    </div>
  </div>
</template>
