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

const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

const calendarDays = computed(() => {
  const year = props.currentDate.getFullYear()
  const month = props.currentDate.getMonth()

  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)

  const startDayOfWeek = firstDay.getDay()
  const daysInMonth = lastDay.getDate()

  const days: Array<{
    date: Date
    isCurrentMonth: boolean
    events: CalendarEvent[]
  }> = []

  // Previous month days
  const prevMonthLastDay = new Date(year, month, 0).getDate()
  for (let i = startDayOfWeek - 1; i >= 0; i--) {
    days.push({
      date: new Date(year, month - 1, prevMonthLastDay - i),
      isCurrentMonth: false,
      events: [],
    })
  }

  // Current month days
  for (let i = 1; i <= daysInMonth; i++) {
    const date = new Date(year, month, i)
    const dayEvents = props.events.filter((event) => {
      const eventDate = new Date(event.date)
      return (
        eventDate.getFullYear() === date.getFullYear() &&
        eventDate.getMonth() === date.getMonth() &&
        eventDate.getDate() === date.getDate()
      )
    })

    days.push({
      date,
      isCurrentMonth: true,
      events: dayEvents,
    })
  }

  // Next month days
  const remainingDays = 42 - days.length // 6 weeks * 7 days
  for (let i = 1; i <= remainingDays; i++) {
    days.push({
      date: new Date(year, month + 1, i),
      isCurrentMonth: false,
      events: [],
    })
  }

  return days
})

const isToday = (date: Date) => {
  const today = new Date()
  return (
    date.getFullYear() === today.getFullYear() &&
    date.getMonth() === today.getMonth() &&
    date.getDate() === today.getDate()
  )
}

const getEventIcon = (event: CalendarEvent) => {
  if (event.type === 'blog') {
    return 'material-symbols-light:article'
  }

  // Social media platform icons
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
</script>

<template>
  <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 overflow-hidden">
    <!-- Days of Week Header -->
    <div class="grid grid-cols-7 border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900">
      <div
        v-for="day in daysOfWeek"
        :key="day"
        class="py-2 text-center text-sm font-semibold text-neutral-700 dark:text-neutral-50"
      >
        {{ day }}
      </div>
    </div>

    <!-- Calendar Grid -->
    <div class="grid grid-cols-7">
      <div
        v-for="(day, index) in calendarDays"
        :key="index"
        :class="[
          'min-h-[120px] border-b border-r border-neutral-200 dark:border-neutral-800 p-2',
          !day.isCurrentMonth && 'bg-neutral-50 dark:bg-neutral-900',
          index % 7 === 6 && 'border-r-0',
        ]"
      >
        <!-- Day Number -->
        <div
          :class="[
            'mb-1 flex h-6 w-6 items-center justify-center rounded-full text-sm',
            isToday(day.date)
              ? 'bg-blue-600 font-bold text-white'
              : day.isCurrentMonth
                ? 'text-neutral-900 dark:text-neutral-100'
                : 'text-neutral-400 dark:text-neutral-600',
          ]"
        >
          {{ day.date.getDate() }}
        </div>

        <!-- Events -->
        <div class="space-y-1">
          <Link
            v-for="event in day.events.slice(0, 3)"
            :key="`${event.type}-${event.id}`"
            :href="event.url"
            class="block rounded px-2 py-1 text-xs font-medium text-white dark:text-neutral-950 hover:opacity-90 transition-opacity"
            :style="{ backgroundColor: event.color }"
          >
            <div class="flex items-center gap-1">
              <Icon :icon="getEventIcon(event)" class="h-3 w-3 flex-shrink-0" />
              <span class="truncate">{{ event.title }}</span>
            </div>
          </Link>

          <!-- More events indicator -->
          <div
            v-if="day.events.length > 3"
            class="px-2 py-1 text-xs font-medium text-neutral-600"
          >
            +{{ day.events.length - 3 }} more
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
