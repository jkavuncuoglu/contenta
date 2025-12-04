<script setup lang="ts">
import { computed, watch, ref } from 'vue'
import { Icon } from '@iconify/vue'
import ConflictWarning from './ConflictWarning.vue'

interface Account {
  id: number
  platform: string
  platform_username: string
  platform_display_name: string
}

interface Form {
  social_account_id: number | null
  content: string
  media_urls: string[]
  link_url: string
  status: string
  scheduled_at: string
}

interface Props {
  form: Form
  accounts: Account[]
}

const props = defineProps<Props>()
const emit = defineEmits<{
  'update:form': [value: Form]
}>()

const localForm = computed({
  get: () => props.form,
  set: (value) => emit('update:form', value),
})

const platformConfig = {
  twitter: { name: 'Twitter/X', limit: 280, mediaLimit: 4 },
  facebook: { name: 'Facebook', limit: 63206, mediaLimit: 10 },
  linkedin: { name: 'LinkedIn', limit: 3000, mediaLimit: 9 },
  instagram: { name: 'Instagram', limit: 2200, mediaLimit: 10 },
  pinterest: { name: 'Pinterest', limit: 500, mediaLimit: 5 },
  tiktok: { name: 'TikTok', limit: 2200, mediaLimit: 1 },
}

const selectedAccount = computed(() => {
  return props.accounts.find((a) => a.id === localForm.value.social_account_id)
})

const selectedPlatformConfig = computed(() => {
  if (!selectedAccount.value) return null
  return platformConfig[selectedAccount.value.platform as keyof typeof platformConfig]
})

const characterCount = computed(() => localForm.value.content.length)

const characterLimit = computed(() => selectedPlatformConfig.value?.limit || 10000)

const isOverLimit = computed(() => characterCount.value > characterLimit.value)

const characterCountColor = computed(() => {
  if (isOverLimit.value) return 'text-red-600'
  if (characterCount.value > characterLimit.value * 0.9) return 'text-yellow-600'
  return 'text-gray-500'
})

const newMediaUrl = ref('')

const addMediaUrl = () => {
  if (newMediaUrl.value.trim()) {
    localForm.value.media_urls.push(newMediaUrl.value.trim())
    newMediaUrl.value = ''
  }
}

const removeMediaUrl = (index: number) => {
  localForm.value.media_urls.splice(index, 1)
}

const conflicts = ref<any>(null)
const checkingConflicts = ref(false)

watch(
  () => [localForm.value.social_account_id, localForm.value.scheduled_at],
  async () => {
    if (localForm.value.social_account_id && localForm.value.scheduled_at) {
      checkingConflicts.value = true
      try {
        const response = await fetch('/admin/social-media/api/posts/check-conflicts', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          },
          body: JSON.stringify({
            social_account_id: localForm.value.social_account_id,
            scheduled_at: localForm.value.scheduled_at,
          }),
        })
        conflicts.value = await response.json()
      } catch (error) {
        console.error('Error checking conflicts:', error)
      } finally {
        checkingConflicts.value = false
      }
    } else {
      conflicts.value = null
    }
  }
)
</script>

<template>
  <div class="space-y-6">
    <!-- Account Selection -->
    <div class="rounded-lg border border-gray-200 bg-white p-6">
      <label class="block text-sm font-medium text-gray-700">Platform Account</label>
      <select
        v-model="localForm.social_account_id"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
        required
      >
        <option v-for="account in accounts" :key="account.id" :value="account.id">
          {{ account.platform.charAt(0).toUpperCase() + account.platform.slice(1) }} -
          @{{ account.platform_username }}
        </option>
      </select>
      <p class="mt-1 text-sm text-gray-500">
        Select which social media account to post to.
      </p>
    </div>

    <!-- Content -->
    <div class="rounded-lg border border-gray-200 bg-white p-6">
      <div class="flex items-center justify-between">
        <label class="block text-sm font-medium text-gray-700">Post Content</label>
        <span :class="['text-sm font-medium', characterCountColor]">
          {{ characterCount }} / {{ characterLimit }}
        </span>
      </div>
      <textarea
        v-model="localForm.content"
        rows="6"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': isOverLimit }"
        placeholder="What do you want to share?"
        required
      ></textarea>
      <p v-if="isOverLimit" class="mt-1 text-sm text-red-600">
        Content exceeds the character limit for {{ selectedPlatformConfig?.name }}.
      </p>
      <p v-else class="mt-1 text-sm text-gray-500">
        Write your post content. Keep it within the character limit for your selected platform.
      </p>
    </div>

    <!-- Media URLs -->
    <div class="rounded-lg border border-gray-200 bg-white p-6">
      <label class="block text-sm font-medium text-gray-700">Media Attachments (Optional)</label>
      <p class="mt-1 text-sm text-gray-500">
        Add up to {{ selectedPlatformConfig?.mediaLimit || 10 }} media URLs (images or videos).
      </p>

      <div class="mt-3 space-y-2">
        <div
          v-for="(url, index) in localForm.media_urls"
          :key="index"
          class="flex items-center gap-2"
        >
          <input
            :value="url"
            type="url"
            disabled
            class="block flex-1 rounded-md border-gray-300 bg-gray-50 text-sm"
          />
          <button
            type="button"
            class="rounded-md border border-red-300 bg-white p-2 text-red-600 hover:bg-red-50"
            @click="removeMediaUrl(index)"
          >
            <Icon icon="mdi:delete" class="h-5 w-5" />
          </button>
        </div>

        <div v-if="localForm.media_urls.length < (selectedPlatformConfig?.mediaLimit || 10)" class="flex items-center gap-2">
          <input
            v-model="newMediaUrl"
            type="url"
            placeholder="https://example.com/image.jpg"
            class="block flex-1 rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
            @keyup.enter="addMediaUrl"
          />
          <button
            type="button"
            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            @click="addMediaUrl"
          >
            Add
          </button>
        </div>
      </div>
    </div>

    <!-- Link URL -->
    <div class="rounded-lg border border-gray-200 bg-white p-6">
      <label class="block text-sm font-medium text-gray-700">Link URL (Optional)</label>
      <input
        v-model="localForm.link_url"
        type="url"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
        placeholder="https://example.com"
      />
      <p class="mt-1 text-sm text-gray-500">
        Add a link to include in your post.
      </p>
    </div>

    <!-- Scheduling -->
    <div class="rounded-lg border border-gray-200 bg-white p-6">
      <label class="block text-sm font-medium text-gray-700">Schedule (Optional)</label>
      <input
        v-model="localForm.scheduled_at"
        type="datetime-local"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
      />
      <p class="mt-1 text-sm text-gray-500">
        Leave empty to save as draft or publish immediately. Set a future date/time to schedule.
      </p>

      <!-- Conflict Warning -->
      <ConflictWarning
        v-if="conflicts?.has_conflicts"
        :conflicts="conflicts.existing_posts"
        class="mt-4"
      />

      <div v-if="checkingConflicts" class="mt-4 flex items-center gap-2 text-sm text-gray-500">
        <Icon icon="mdi:loading" class="h-4 w-4 animate-spin" />
        <span>Checking for conflicts...</span>
      </div>
    </div>
  </div>
</template>
