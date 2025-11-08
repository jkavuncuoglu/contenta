<template>
  <Head title="Create Menu" />

  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Create Menu</h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Create a new navigation menu for your site
          </p>
        </div>
        <Link
          :href="'/admin/menus'"
          class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Menus
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-3xl">
        <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
          <div class="p-6 space-y-6">
            <!-- Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Menu Name <span class="text-red-500">*</span>
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="e.g., Main Navigation"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.name }}
              </p>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                A descriptive name for this menu (displayed in admin only)
              </p>
            </div>

            <!-- Slug -->
            <div>
              <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Slug
              </label>
              <input
                id="slug"
                v-model="form.slug"
                type="text"
                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Auto-generated from name"
              />
              <p v-if="errors.slug" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.slug }}
              </p>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                URL-friendly identifier (auto-generated if left empty)
              </p>
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Description
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Optional description of this menu's purpose"
              />
              <p v-if="errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.description }}
              </p>
            </div>

            <!-- Location -->
            <div>
              <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Menu Location
              </label>
              <select
                id="location"
                v-model="form.location"
                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">None (Unassigned)</option>
                <option v-for="(label, value) in locations" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
              <p v-if="errors.location" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.location }}
              </p>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Where this menu will be displayed on your site
              </p>
            </div>

            <!-- Active Status -->
            <div class="flex items-center">
              <input
                id="is_active"
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                Active (display this menu on the site)
              </label>
            </div>
          </div>

          <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3">
            <Link
              :href="'/admin/menus'"
              class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="processing"
              class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ processing ? 'Creating...' : 'Create Menu' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Props {
  locations: Record<string, string>
  errors?: Record<string, string>
}

const props = withDefaults(defineProps<Props>(), {
  errors: () => ({}),
})

const form = reactive({
  name: '',
  slug: '',
  description: '',
  location: '',
  is_active: true,
})

const processing = ref(false)
const errors = ref(props.errors)

const submit = async () => {
  processing.value = true
  errors.value = {}

  try {
    const response = await fetch('/admin/menus/api', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify(form),
    })

    if (response.ok) {
      const menu = await response.json()
      router.visit('/admin/menus/' + menu.id + '/edit')
    } else {
      const errorData = await response.json()
      errors.value = errorData.errors || {}
    }
  } catch (error) {
    console.error('Failed to create menu:', error)
    alert('Failed to create menu. Please try again.')
  } finally {
    processing.value = false
  }
}
</script>
