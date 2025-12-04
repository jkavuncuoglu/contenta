<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { computed } from 'vue'

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
  account: {
    id: number
    platform: string
    platform_username: string
  }
  created_at: string
}

interface Props {
  post: Post
}

const props = defineProps<Props>()

const platformConfig = {
  twitter: { name: 'Twitter/X', icon: 'mdi:twitter', color: '#1DA1F2' },
  facebook: { name: 'Facebook', icon: 'mdi:facebook', color: '#1877F2' },
  linkedin: { name: 'LinkedIn', icon: 'mdi:linkedin', color: '#0A66C2' },
  instagram: { name: 'Instagram', icon: 'mdi:instagram', color: '#E4405F' },
  pinterest: { name: 'Pinterest', icon: 'mdi:pinterest', color: '#BD081C' },
  tiktok: { name: 'TikTok', icon: 'simple-icons:tiktok', color: '#000000' },
}

const config = computed(
  () => platformConfig[props.post.account.platform as keyof typeof platformConfig] || {},
)

const statusConfig = {
  draft: { label: 'Draft', color: 'bg-gray-100 text-gray-800' },
  scheduled: { label: 'Scheduled', color: 'bg-blue-100 text-blue-800' },
  publishing: { label: 'Publishing', color: 'bg-yellow-100 text-yellow-800' },
  published: { label: 'Published', color: 'bg-green-100 text-green-800' },
  failed: { label: 'Failed', color: 'bg-red-100 text-red-800' },
}

const status = computed(() => statusConfig[props.post.status as keyof typeof statusConfig])

const contentPreview = computed(() => {
  const maxLength = 150
  return props.post.content.length > maxLength
    ? props.post.content.substring(0, maxLength) + '...'
    : props.post.content
})

const formattedDate = (dateString: string | null) => {
  if (!dateString) return null
  const date = new Date(dateString)
  return date.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  })
}

const canEdit = computed(() => {
  return props.post.status === 'draft' || props.post.status === 'scheduled'
})

const canPublish = computed(() => {
  return props.post.status === 'draft' || props.post.status === 'scheduled' || props.post.status === 'failed'
})

const canRetry = computed(() => {
  return props.post.status === 'failed' && props.post.retry_count < 3
})

const canCancel = computed(() => {
  return props.post.status === 'scheduled'
})

const handlePublish = () => {
  if (confirm('Publish this post now?')) {
    router.post(`/admin/social-media/posts/${props.post.id}/publish`)
  }
}

const handleRetry = () => {
  if (confirm('Retry publishing this post?')) {
    router.post(`/admin/social-media/posts/${props.post.id}/retry`)
  }
}

const handleCancel = () => {
  if (confirm('Cancel scheduling and revert to draft?')) {
    router.post(`/admin/social-media/posts/${props.post.id}/cancel`)
  }
}

const handleDelete = () => {
  if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
    router.delete(`/admin/social-media/posts/${props.post.id}`)
  }
}
</script>

<template>
  <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
    <!-- Header -->
    <div class="flex items-start justify-between">
      <div class="flex items-center gap-3">
        <div
          class="flex h-10 w-10 items-center justify-center rounded-lg"
          :style="{ backgroundColor: config.color + '20' }"
        >
          <Icon :icon="config.icon" class="h-5 w-5" :style="{ color: config.color }" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900">{{ config.name }}</h3>
          <p class="text-sm text-gray-500">@{{ post.account.platform_username }}</p>
        </div>
      </div>

      <span
        :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', status.color]"
      >
        {{ status.label }}
      </span>
    </div>

    <!-- Content Preview -->
    <div class="mt-4">
      <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ contentPreview }}</p>
    </div>

    <!-- Metadata -->
    <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-500">
      <div v-if="post.scheduled_at" class="flex items-center gap-1">
        <Icon icon="material-symbols-light:schedule" class="h-4 w-4" />
        <span>Scheduled: {{ formattedDate(post.scheduled_at) }}</span>
      </div>
      <div v-if="post.published_at" class="flex items-center gap-1">
        <Icon icon="material-symbols-light:check-circle" class="h-4 w-4" />
        <span>Published: {{ formattedDate(post.published_at) }}</span>
      </div>
      <div class="flex items-center gap-1">
        <Icon
          :icon="post.source_type === 'manual' ? 'material-symbols-light:edit' : 'material-symbols-light:auto-awesome'"
          class="h-4 w-4"
        />
        <span>{{ post.source_type === 'manual' ? 'Manual' : 'Auto-generated' }}</span>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="post.error_message" class="mt-4 rounded-md bg-red-50 p-3">
      <div class="flex">
        <Icon icon="mdi:alert-circle" class="h-5 w-5 text-red-400" />
        <div class="ml-3">
          <p class="text-sm text-red-800">{{ post.error_message }}</p>
          <p v-if="post.retry_count > 0" class="mt-1 text-xs text-red-600">
            Retry attempts: {{ post.retry_count }}/3
          </p>
        </div>
      </div>
    </div>

    <!-- Platform Link -->
    <div v-if="post.platform_permalink" class="mt-4">
      <a
        :href="post.platform_permalink"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700"
      >
        <Icon icon="material-symbols-light:open-in-new" class="h-4 w-4" />
        View on {{ config.name }}
      </a>
    </div>

    <!-- Actions -->
    <div class="mt-6 flex flex-wrap gap-2">
      <Link
        v-if="canEdit"
        :href="`/admin/social-media/posts/${post.id}/edit`"
        class="rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200"
      >
        Edit
      </Link>
      <button
        v-if="canPublish && !canRetry"
        type="button"
        class="rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
        @click="handlePublish"
      >
        Publish Now
      </button>
      <button
        v-if="canRetry"
        type="button"
        class="rounded-md bg-orange-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-orange-700"
        @click="handleRetry"
      >
        Retry ({{ 3 - post.retry_count }} left)
      </button>
      <button
        v-if="canCancel"
        type="button"
        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
        @click="handleCancel"
      >
        Cancel Schedule
      </button>
      <Link
        :href="`/admin/social-media/posts/${post.id}`"
        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
      >
        View Details
      </Link>
      <button
        type="button"
        class="rounded-md border border-red-300 bg-white px-3 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50"
        @click="handleDelete"
      >
        Delete
      </button>
    </div>
  </div>
</template>
