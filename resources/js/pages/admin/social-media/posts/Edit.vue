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

interface Post {
  id: number
  social_account_id: number
  content: string
  media_urls: string[]
  link_url: string
  status: string
  scheduled_at: string
}

interface Props {
  post: Post
  accounts: Account[]
  breadcrumbs: Array<{ label: string; href?: string }>
}

const props = defineProps<Props>()

const form = useForm({
  social_account_id: props.post.social_account_id,
  content: props.post.content,
  media_urls: props.post.media_urls || [],
  link_url: props.post.link_url || '',
  status: props.post.status,
  scheduled_at: props.post.scheduled_at || '',
})

const canEdit = props.post.status === 'draft' || props.post.status === 'scheduled'

const handleUpdate = () => {
  form.put(`/admin/social-media/posts/${props.post.id}`)
}

const handleSaveDraft = () => {
  form.status = 'draft'
  form.scheduled_at = ''
  form.put(`/admin/social-media/posts/${props.post.id}`)
}

const handleSchedule = () => {
  form.status = 'scheduled'
  form.put(`/admin/social-media/posts/${props.post.id}`)
}

const handlePublishNow = () => {
  if (confirm('Publish this post immediately?')) {
    form.status = 'scheduled'
    form.scheduled_at = ''
    form.put(`/admin/social-media/posts/${props.post.id}`)
  }
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Edit Social Media Post" />

    <div class="mx-auto max-w-4xl space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Edit Social Media Post</h1>
        <p class="mt-2 text-sm text-gray-600">
          Update your post and republish or reschedule it.
        </p>
      </div>

      <!-- Cannot Edit Warning -->
      <div v-if="!canEdit" class="rounded-md bg-red-50 p-4">
        <div class="flex">
          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
              clip-rule="evenodd"
            />
          </svg>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Cannot edit published posts</h3>
            <div class="mt-2 text-sm text-red-700">
              <p>
                This post has already been published or is currently publishing. You cannot edit
                it at this time.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div v-if="canEdit">
        <PostForm v-model:form="form" :accounts="accounts" />

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-between rounded-lg border border-gray-200 bg-white p-6">
          <a
            href="/admin/social-media/posts"
            class="text-sm font-medium text-gray-600 hover:text-gray-900"
          >
            Cancel
          </a>
          <div class="flex gap-3">
            <button
              v-if="post.status === 'scheduled'"
              type="button"
              :disabled="form.processing"
              class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
              @click="handleSaveDraft"
            >
              Revert to Draft
            </button>
            <button
              v-if="post.status === 'draft'"
              type="button"
              :disabled="form.processing || !form.scheduled_at"
              class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
              @click="handleSchedule"
            >
              Schedule
            </button>
            <button
              v-else
              type="button"
              :disabled="form.processing"
              class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
              @click="handleUpdate"
            >
              Update
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

      <!-- Read-Only View for Published Posts -->
      <div v-else class="rounded-lg border border-gray-200 bg-gray-50 p-6">
        <h3 class="mb-4 text-lg font-semibold">Post Details</h3>
        <dl class="space-y-3">
          <div>
            <dt class="text-sm font-medium text-gray-500">Content</dt>
            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ post.content }}</dd>
          </div>
          <div v-if="post.media_urls && post.media_urls.length > 0">
            <dt class="text-sm font-medium text-gray-500">Media</dt>
            <dd class="mt-1 space-y-1">
              <a
                v-for="(url, index) in post.media_urls"
                :key="index"
                :href="url"
                target="_blank"
                class="block text-sm text-blue-600 hover:underline"
              >
                {{ url }}
              </a>
            </dd>
          </div>
          <div v-if="post.link_url">
            <dt class="text-sm font-medium text-gray-500">Link</dt>
            <dd class="mt-1">
              <a :href="post.link_url" target="_blank" class="text-sm text-blue-600 hover:underline">
                {{ post.link_url }}
              </a>
            </dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Status</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ post.status }}</dd>
          </div>
        </dl>
        <div class="mt-6">
          <a
            href="/admin/social-media/posts"
            class="text-sm font-medium text-blue-600 hover:text-blue-700"
          >
            ← Back to Posts
          </a>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
