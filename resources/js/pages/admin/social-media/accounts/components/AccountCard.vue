<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { computed } from 'vue'

interface Account {
  id: number
  platform: string
  platform_username: string | null
  platform_display_name: string | null
  is_active: boolean
  auto_post_enabled: boolean
  auto_post_mode: string
  scheduled_post_time: string | null
  token_expires_at: string | null
  last_synced_at: string | null
  created_at: string
  recent_posts_count: number
}

interface Props {
  account: Account
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
  () => platformConfig[props.account.platform as keyof typeof platformConfig] || {},
)

const tokenExpired = computed(() => {
  if (!props.account.token_expires_at) return false
  return new Date(props.account.token_expires_at) < new Date()
})

const tokenExpiringSoon = computed(() => {
  if (!props.account.token_expires_at || tokenExpired.value) return false
  const expiresAt = new Date(props.account.token_expires_at)
  const oneHourFromNow = new Date(Date.now() + 60 * 60 * 1000)
  return expiresAt < oneHourFromNow
})

const handleDisconnect = () => {
  if (confirm(`Are you sure you want to disconnect this ${config.value.name} account?`)) {
    router.post(`/admin/social-media/oauth/${props.account.id}/disconnect`)
  }
}

const handleRefreshToken = () => {
  router.post(`/admin/social-media/oauth/${props.account.id}/refresh`)
}

const handleVerify = () => {
  router.post(`/admin/social-media/accounts/${props.account.id}/verify`)
}
</script>

<template>
  <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
    <!-- Header -->
    <div class="flex items-start justify-between">
      <div class="flex items-center gap-3">
        <div
          class="flex h-12 w-12 items-center justify-center rounded-lg"
          :style="{ backgroundColor: config.color + '20' }"
        >
          <Icon :icon="config.icon" class="h-6 w-6" :style="{ color: config.color }" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900">{{ config.name }}</h3>
          <p class="text-sm text-gray-500">
            {{ account.platform_display_name || account.platform_username || 'Connected' }}
          </p>
        </div>
      </div>

      <!-- Status Badge -->
      <span
        v-if="!account.is_active"
        class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800"
      >
        Inactive
      </span>
      <span
        v-else-if="tokenExpired"
        class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800"
      >
        Token Expired
      </span>
      <span
        v-else-if="tokenExpiringSoon"
        class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800"
      >
        Expiring Soon
      </span>
      <span
        v-else
        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"
      >
        Active
      </span>
    </div>

    <!-- Details -->
    <div class="mt-4 space-y-2 text-sm">
      <div class="flex items-center justify-between">
        <span class="text-gray-600">Auto-posting:</span>
        <span class="font-medium text-gray-900">
          {{ account.auto_post_enabled ? 'Enabled' : 'Disabled' }}
        </span>
      </div>
      <div v-if="account.auto_post_enabled" class="flex items-center justify-between">
        <span class="text-gray-600">Mode:</span>
        <span class="font-medium text-gray-900">
          {{ account.auto_post_mode === 'immediate' ? 'Immediate' : 'Scheduled' }}
        </span>
      </div>
      <div
        v-if="account.auto_post_enabled && account.auto_post_mode === 'scheduled'"
        class="flex items-center justify-between"
      >
        <span class="text-gray-600">Post time:</span>
        <span class="font-medium text-gray-900">
          {{ account.scheduled_post_time }}
        </span>
      </div>
      <div class="flex items-center justify-between">
        <span class="text-gray-600">Recent posts:</span>
        <span class="font-medium text-gray-900">
          {{ account.recent_posts_count }}
        </span>
      </div>
    </div>

    <!-- Token Warning -->
    <div v-if="tokenExpired" class="mt-4 rounded-md bg-red-50 p-3">
      <div class="flex">
        <Icon icon="mdi:alert-circle" class="h-5 w-5 text-red-400" />
        <div class="ml-3">
          <p class="text-sm text-red-800">
            Access token has expired. Refresh to continue posting.
          </p>
        </div>
      </div>
    </div>

    <div v-else-if="tokenExpiringSoon" class="mt-4 rounded-md bg-yellow-50 p-3">
      <div class="flex">
        <Icon icon="mdi:alert" class="h-5 w-5 text-yellow-400" />
        <div class="ml-3">
          <p class="text-sm text-yellow-800">
            Access token expires soon. Consider refreshing.
          </p>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="mt-6 flex gap-2">
      <Link
        :href="`/admin/social-media/accounts/${account.id}/edit`"
        class="flex-1 rounded-md bg-gray-100 px-3 py-2 text-center text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200"
      >
        Edit Settings
      </Link>
      <button
        v-if="tokenExpired || tokenExpiringSoon"
        type="button"
        class="rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
        @click="handleRefreshToken"
      >
        Refresh Token
      </button>
      <button
        type="button"
        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
        @click="handleVerify"
      >
        Verify
      </button>
      <button
        type="button"
        class="rounded-md border border-red-300 bg-white px-3 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50"
        @click="handleDisconnect"
      >
        Disconnect
      </button>
    </div>
  </div>
</template>
