<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/layouts/AppLayout.vue'
import AutoPostSettings from './components/AutoPostSettings.vue'

interface Account {
  id: number
  platform: string
  platform_username: string | null
  platform_display_name: string | null
  is_active: boolean
  auto_post_enabled: boolean
  auto_post_mode: string
  scheduled_post_time: string | null
  platform_settings: Record<string, any> | null
}

interface Props {
  account: Account
  breadcrumbs: Array<{ label: string; href?: string }>
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

const config = platformConfig[props.account.platform as keyof typeof platformConfig] || {}

const form = useForm({
  is_active: props.account.is_active,
  auto_post_enabled: props.account.auto_post_enabled,
  auto_post_mode: props.account.auto_post_mode,
  scheduled_post_time: props.account.scheduled_post_time || '09:00:00',
  platform_settings: props.account.platform_settings || {},
})

const submit = () => {
  form.put(`/admin/social-media/accounts/${props.account.id}`)
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head :title="`Edit ${config.name} Account`" />

    <div class="mx-auto max-w-3xl space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <div
          class="flex h-16 w-16 items-center justify-center rounded-lg"
          :style="{ backgroundColor: config.color + '20' }"
        >
          <Icon :icon="config.icon" class="h-8 w-8" :style="{ color: config.color }" />
        </div>
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Edit {{ config.name }} Account</h1>
          <p class="mt-1 text-sm text-gray-600">
            {{ account.platform_display_name || account.platform_username || 'Connected Account' }}
          </p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Account Status -->
        <div class="rounded-lg border border-gray-200 bg-white p-6">
          <h2 class="text-lg font-semibold text-gray-900">Account Status</h2>
          <p class="mt-1 text-sm text-gray-600">
            Enable or disable this social media account.
          </p>

          <div class="mt-4">
            <label class="flex items-center gap-3">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <div>
                <span class="text-sm font-medium text-gray-900">Account Active</span>
                <p class="text-sm text-gray-500">
                  When disabled, no posts will be published to this account.
                </p>
              </div>
            </label>
          </div>
        </div>

        <!-- Auto-Post Settings -->
        <AutoPostSettings v-model:form="form" :platform="account.platform" />

        <!-- Platform-Specific Settings -->
        <div
          v-if="account.platform === 'facebook' || account.platform === 'linkedin'"
          class="rounded-lg border border-gray-200 bg-white p-6"
        >
          <h2 class="text-lg font-semibold text-gray-900">Platform Settings</h2>
          <p class="mt-1 text-sm text-gray-600">
            Configure platform-specific options.
          </p>

          <div class="mt-4 space-y-4">
            <div v-if="account.platform === 'facebook'">
              <label class="block text-sm font-medium text-gray-700">Facebook Page ID</label>
              <input
                v-model="form.platform_settings.page_id"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                placeholder="Enter your Facebook Page ID"
              />
              <p class="mt-1 text-sm text-gray-500">
                Required for posting to your Facebook Page.
              </p>
            </div>

            <div v-if="account.platform === 'linkedin'">
              <label class="block text-sm font-medium text-gray-700">Author URN</label>
              <input
                v-model="form.platform_settings.author_urn"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                placeholder="urn:li:person:12345"
              />
              <p class="mt-1 text-sm text-gray-500">
                LinkedIn person or organization URN.
              </p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
          <a
            href="/admin/social-media/accounts"
            class="text-sm font-medium text-gray-600 hover:text-gray-900"
          >
            Cancel
          </a>
          <button
            type="submit"
            :disabled="form.processing"
            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>

        <!-- Error Messages -->
        <div v-if="form.errors && Object.keys(form.errors).length > 0" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <Icon icon="mdi:alert-circle" class="h-5 w-5 text-red-400" />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
              <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                  <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
