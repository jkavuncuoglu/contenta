<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import PostForm from './components/PostForm.vue'

interface Account {
  id: number
  platform: string
  platform_username: string
  platform_display_name: string
}

interface Props {
  accounts: Account[]
  breadcrumbs: Array<{ label: string; href?: string }>
}

const props = defineProps<Props>()

const form = useForm({
  social_account_id: props.accounts.length > 0 ? props.accounts[0].id : null,
  content: '',
  media_urls: [] as string[],
  link_url: '',
  status: 'draft',
  scheduled_at: '',
})

const handleSaveDraft = () => {
  form.status = 'draft'
  form.post('/admin/social-media/posts')
}

const handleSchedule = () => {
  form.status = 'scheduled'
  form.post('/admin/social-media/posts')
}

const handlePublishNow = () => {
  if (confirm('Publish this post immediately?')) {
    form.status = 'scheduled' // Will be published immediately by backend
    form.scheduled_at = ''
    form.post('/admin/social-media/posts')
  }
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Create Social Media Post" />

    <div class="mx-auto max-w-4xl space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Create Social Media Post</h1>
        <p class="mt-2 text-sm text-neutral-600">
          Compose a new post and publish it immediately or schedule it for later.
        </p>
      </div>

      <!-- No Accounts Warning -->
      <div v-if="accounts.length === 0" class="rounded-md bg-yellow-50 p-4">
        <div class="flex">
          <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
            <path
              fill-rule="evenodd"
              d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
              clip-rule="evenodd"
            />
          </svg>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">No social accounts connected</h3>
            <div class="mt-2 text-sm text-yellow-700">
              <p>
                You need to connect at least one social media account before creating posts.
                <a href="/admin/social-media/accounts" class="font-medium underline hover:text-yellow-600">
                  Connect an account
                </a>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div v-else>
        <PostForm v-model:form="form" :accounts="accounts" />

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-between rounded-lg border border-neutral-200 bg-white p-6">
          <a
            href="/admin/social-media/posts"
            class="text-sm font-medium text-neutral-600 hover:text-neutral-900"
          >
            Cancel
          </a>
          <div class="flex gap-3">
            <button
              type="button"
              :disabled="form.processing"
              class="rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 shadow-sm transition-colors hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-50"
              @click="handleSaveDraft"
            >
              Save as Draft
            </button>
            <button
              type="button"
              :disabled="form.processing || !form.scheduled_at"
              class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
              @click="handleSchedule"
            >
              Schedule
            </button>
            <button
              type="button"
              :disabled="form.processing"
              class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
              @click="handlePublishNow"
            >
              Publish Now
            </button>
          </div>
        </div>

        <!-- Error Messages -->
        <div v-if="form.errors && Object.keys(form.errors).length > 0" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                clip-rule="evenodd"
              />
            </svg>
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
      </div>
    </div>
  </AppLayout>
</template>
