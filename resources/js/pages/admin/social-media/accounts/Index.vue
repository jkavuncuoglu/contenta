<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ConnectPlatformButton from './components/ConnectPlatformButton.vue'
import AccountCard from './components/AccountCard.vue'

interface SocialAccount {
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
  accounts: SocialAccount[]
  breadcrumbs: Array<{ label: string; href?: string }>
}

const props = defineProps<Props>()

const platforms = [
  { key: 'twitter', name: 'Twitter/X', icon: 'mdi:twitter', color: '#1DA1F2' },
  { key: 'facebook', name: 'Facebook', icon: 'mdi:facebook', color: '#1877F2' },
  { key: 'linkedin', name: 'LinkedIn', icon: 'mdi:linkedin', color: '#0A66C2' },
  { key: 'instagram', name: 'Instagram', icon: 'mdi:instagram', color: '#E4405F' },
  { key: 'pinterest', name: 'Pinterest', icon: 'mdi:pinterest', color: '#BD081C' },
  { key: 'tiktok', name: 'TikTok', icon: 'simple-icons:tiktok', color: '#000000' },
]

const connectedPlatforms = new Set(props.accounts.map((a) => a.platform))
const availablePlatforms = platforms.filter((p) => !connectedPlatforms.has(p.key))
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Social Media Accounts" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Social Media Accounts</h1>
          <p class="mt-2 text-sm text-gray-600">
            Manage connected social media accounts for auto-posting and scheduling.
          </p>
        </div>
      </div>

      <!-- Connect New Account Section -->
      <div v-if="availablePlatforms.length > 0" class="rounded-lg border border-gray-200 bg-white p-6">
        <h2 class="text-lg font-semibold text-gray-900">Connect New Platform</h2>
        <p class="mt-1 text-sm text-gray-600">
          Connect your social media accounts to enable auto-posting and scheduling.
        </p>
        <div class="mt-4 flex flex-wrap gap-3">
          <ConnectPlatformButton
            v-for="platform in availablePlatforms"
            :key="platform.key"
            :platform="platform.key"
            :name="platform.name"
            :icon="platform.icon"
            :color="platform.color"
          />
        </div>
      </div>

      <!-- Connected Accounts -->
      <div v-if="accounts.length > 0" class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Connected Accounts</h2>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          <AccountCard v-for="account in accounts" :key="account.id" :account="account" />
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="accounts.length === 0"
        class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-12 text-center"
      >
        <svg
          class="mx-auto h-12 w-12 text-gray-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M13 10V3L4 14h7v7l9-11h-7z"
          />
        </svg>
        <h3 class="mt-2 text-sm font-semibold text-gray-900">No accounts connected</h3>
        <p class="mt-1 text-sm text-gray-500">
          Get started by connecting your first social media account.
        </p>
      </div>
    </div>
  </AppLayout>
</template>
