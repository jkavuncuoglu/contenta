<script setup lang="ts">
import { computed } from 'vue'

interface Form {
  auto_post_enabled: boolean
  auto_post_mode: string
  scheduled_post_time: string
}

interface Props {
  form: Form
  platform: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:form': [value: Form]
}>()

const localForm = computed({
  get: () => props.form,
  set: (value) => emit('update:form', value),
})
</script>

<template>
  <div class="rounded-lg border border-gray-200 bg-white p-6">
    <h2 class="text-lg font-semibold text-gray-900">Auto-Posting</h2>
    <p class="mt-1 text-sm text-gray-600">
      Automatically post new blog posts to this social media account.
    </p>

    <div class="mt-4 space-y-4">
      <!-- Enable Auto-Post -->
      <div>
        <label class="flex items-center gap-3">
          <input
            v-model="localForm.auto_post_enabled"
            type="checkbox"
            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
          />
          <div>
            <span class="text-sm font-medium text-gray-900">Enable Auto-Posting</span>
            <p class="text-sm text-gray-500">
              Automatically create social posts when blog posts are published.
            </p>
          </div>
        </label>
      </div>

      <!-- Auto-Post Mode -->
      <div v-if="localForm.auto_post_enabled" class="ml-7 space-y-3 border-l-2 border-gray-200 pl-4">
        <div class="space-y-2">
          <label class="flex items-start gap-3">
            <input
              v-model="localForm.auto_post_mode"
              type="radio"
              value="immediate"
              class="mt-0.5 h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <div>
              <span class="text-sm font-medium text-gray-900">Post Immediately</span>
              <p class="text-sm text-gray-500">
                Publish to {{ platform }} as soon as the blog post is published.
              </p>
            </div>
          </label>

          <label class="flex items-start gap-3">
            <input
              v-model="localForm.auto_post_mode"
              type="radio"
              value="scheduled"
              class="mt-0.5 h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <div class="flex-1">
              <span class="text-sm font-medium text-gray-900">Schedule for Later</span>
              <p class="text-sm text-gray-500">
                Queue posts and publish at a specific time each day.
              </p>

              <!-- Scheduled Time Input -->
              <div v-if="localForm.auto_post_mode === 'scheduled'" class="mt-2">
                <label class="block text-sm font-medium text-gray-700">Daily Post Time</label>
                <input
                  v-model="localForm.scheduled_post_time"
                  type="time"
                  class="mt-1 block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                />
                <p class="mt-1 text-xs text-gray-500">
                  Posts will be published daily at this time (24-hour format).
                </p>
              </div>
            </div>
          </label>
        </div>
      </div>

      <!-- Info Box -->
      <div
        v-if="localForm.auto_post_enabled"
        class="rounded-md bg-blue-50 p-4"
      >
        <div class="flex">
          <svg
            class="h-5 w-5 text-blue-400"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
              clip-rule="evenodd"
            />
          </svg>
          <div class="ml-3">
            <p class="text-sm text-blue-800">
              <strong>How it works:</strong> When you publish a blog post, a social media post will be
              automatically generated with the blog title and a link. You can review and edit these
              posts before they go live.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
