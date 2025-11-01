<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Icon } from '@iconify/vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import Input from '@/components/ui/input/Input.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';

interface Settings {
  [key: string]: {
    value: string | boolean | number;
    type: string;
    description: string;
  };
}

interface Options {
  languages: { [key: string]: string };
  timezones: { [key: string]: string };
  userRoles: { [key: string]: string };
  pages: { [key: string]: string };
}

interface Props {
  settings: Settings;
  options: Options;
}

const props = defineProps<Props>();

const page = usePage();
const flashMessage = computed(() => page.props.flash as { success?: string; error?: string } | null);

const form = useForm({
  site_title: props.settings.site_title?.value || '',
  site_tagline: props.settings.site_tagline?.value || '',
  site_url: props.settings.site_url?.value || '',
  admin_email: props.settings.site_admin_email?.value || '',
  users_can_register: props.settings.site_users_can_register?.value || false,
  default_user_role: props.settings.site_default_user_role?.value || 'subscriber',
  site_language: props.settings.site_language?.value || 'en_US',
  timezone: props.settings.site_timezone?.value || 'UTC',
  landing_page: props.settings.site_landing_page?.value || 'blog',
  google_analytics_id: props.settings.site_google_analytics_id?.value || '',
  google_tag_manager_id: props.settings.site_google_tag_manager_id?.value || '',
  facebook_pixel_id: props.settings.site_facebook_pixel_id?.value || '',
  analytics_enabled: props.settings.site_analytics_enabled?.value || true,
  cookie_consent_enabled: props.settings.site_cookie_consent_enabled?.value || true,
});

const submit = () => {
  form.put('/admin/settings/site', {
    preserveScroll: true,
  });
};

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Settings',
  },
  {
    title: 'Site',
  },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Site Settings" />

    <div class="space-y-6">
      <HeadingSmall
        title="General Settings"
        description="Configure your site's basic information and preferences"
      />

      <!-- Flash Messages -->
      <div v-if="flashMessage?.success" class="bg-green-50 border border-green-200 rounded-md p-4">
        <div class="flex">
          <Icon icon="material-symbols-light:check-circle" class="h-5 w-5 text-green-400" />
          <div class="ml-3">
            <p class="text-sm text-green-800">{{ flashMessage.success }}</p>
          </div>
        </div>
      </div>

      <div v-if="flashMessage?.error" class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <Icon icon="material-symbols-light:error" class="h-5 w-5 text-red-400" />
          <div class="ml-3">
            <p class="text-sm text-red-800">{{ flashMessage.error }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <form @submit.prevent="submit">
          <div class="px-6 py-6 space-y-6">
            <!-- Site Title -->
            <div>
              <label for="site_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Site Title
              </label>
              <div class="mt-1">
                <Input
                  id="site_title"
                  v-model="form.site_title"
                  type="text"
                  class="block w-full"
                  :class="{ 'border-red-300': form.errors.site_title }"
                />
              </div>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                In a few words, explain what this site is about.
              </p>
              <div v-if="form.errors.site_title" class="mt-2 text-sm text-red-600">
                {{ form.errors.site_title }}
              </div>
            </div>

            <!-- Tagline -->
            <div>
              <label for="site_tagline" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Tagline
              </label>
              <div class="mt-1">
                <Input
                  id="site_tagline"
                  v-model="form.site_tagline"
                  type="text"
                  class="block w-full"
                  :class="{ 'border-red-300': form.errors.site_tagline }"
                />
              </div>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                In a few words, explain what this site is about.
              </p>
              <div v-if="form.errors.site_tagline" class="mt-2 text-sm text-red-600">
                {{ form.errors.site_tagline }}
              </div>
            </div>

            <!-- Site URL -->
            <div>
              <label for="site_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Site Address (URL)
              </label>
              <div class="mt-1">
                <Input
                  id="site_url"
                  v-model="form.site_url"
                  type="url"
                  class="block w-full"
                  :class="{ 'border-red-300': form.errors.site_url }"
                />
              </div>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                The address of your site.
              </p>
              <div v-if="form.errors.site_url" class="mt-2 text-sm text-red-600">
                {{ form.errors.site_url }}
              </div>
            </div>

            <!-- Administration Email -->
            <div>
              <label for="admin_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Administration Email Address
              </label>
              <div class="mt-1">
                <Input
                  id="admin_email"
                  v-model="form.admin_email"
                  type="email"
                  class="block w-full"
                  :class="{ 'border-red-300': form.errors.admin_email }"
                />
              </div>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                This address is used for admin purposes, like new user notification.
              </p>
              <div v-if="form.errors.admin_email" class="mt-2 text-sm text-red-600">
                {{ form.errors.admin_email }}
              </div>
            </div>

            <!-- Membership -->
            <div>
              <div class="flex items-center">
                <input
                  id="users_can_register"
                  v-model="form.users_can_register"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="users_can_register" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                  Anyone can register
                </label>
              </div>
              <div v-if="form.errors.users_can_register" class="mt-2 text-sm text-red-600">
                {{ form.errors.users_can_register }}
              </div>
            </div>

            <!-- New User Default Role -->
            <div>
              <label for="default_user_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                New User Default Role
              </label>
              <div class="mt-1">
                <select
                  id="default_user_role"
                  v-model="form.default_user_role"
                  class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="{ 'border-red-300': form.errors.default_user_role }"
                >
                  <option v-for="(label, value) in options.userRoles" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
              </div>
              <div v-if="form.errors.default_user_role" class="mt-2 text-sm text-red-600">
                {{ form.errors.default_user_role }}
              </div>
            </div>

            <!-- Site Language -->
            <div>
              <label for="site_language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Site Language
              </label>
              <div class="mt-1">
                <select
                  id="site_language"
                  v-model="form.site_language"
                  class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="{ 'border-red-300': form.errors.site_language }"
                >
                  <option v-for="(label, value) in options.languages" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
              </div>
              <div v-if="form.errors.site_language" class="mt-2 text-sm text-red-600">
                {{ form.errors.site_language }}
              </div>
            </div>

            <!-- Timezone -->
            <div>
              <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Timezone
              </label>
              <div class="mt-1">
                <select
                  id="timezone"
                  v-model="form.timezone"
                  class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="{ 'border-red-300': form.errors.timezone }"
                >
                  <option v-for="(label, value) in options.timezones" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
              </div>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Choose either a city in the same timezone as you or a UTC timezone offset.
              </p>
              <div v-if="form.errors.timezone" class="mt-2 text-sm text-red-600">
                {{ form.errors.timezone }}
              </div>
            </div>

            <!-- Landing Page -->
            <div>
              <label for="landing_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Landing Page
              </label>
              <div class="mt-1">
                <select
                  id="landing_page"
                  v-model="form.landing_page"
                  class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="{ 'border-red-300': form.errors.landing_page }"
                >
                  <option v-for="(label, value) in options.pages" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
              </div>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Choose what content visitors see when they visit your site's homepage.
              </p>
              <div v-if="form.errors.landing_page" class="mt-2 text-sm text-red-600">
                {{ form.errors.landing_page }}
              </div>
            </div>

            <!-- Analytics Section -->
            <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Analytics & Tracking</h3>

              <!-- Analytics Enabled -->
              <div class="mb-6">
                <div class="flex items-center">
                  <input
                    id="analytics_enabled"
                    v-model="form.analytics_enabled"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="analytics_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Enable analytics tracking
                  </label>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                  Master switch to enable/disable all analytics tracking on your site.
                </p>
                <div v-if="form.errors.analytics_enabled" class="mt-2 text-sm text-red-600">
                  {{ form.errors.analytics_enabled }}
                </div>
              </div>

              <!-- Google Analytics -->
              <div class="mb-6">
                <label for="google_analytics_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Google Analytics Measurement ID
                </label>
                <div class="mt-1">
                  <Input
                    id="google_analytics_id"
                    v-model="form.google_analytics_id"
                    type="text"
                    placeholder="G-XXXXXXXXXX"
                    class="block w-full"
                    :class="{ 'border-red-300': form.errors.google_analytics_id }"
                  />
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                  Your Google Analytics 4 Measurement ID (starts with G-).
                </p>
                <div v-if="form.errors.google_analytics_id" class="mt-2 text-sm text-red-600">
                  {{ form.errors.google_analytics_id }}
                </div>
              </div>

              <!-- Google Tag Manager -->
              <div class="mb-6">
                <label for="google_tag_manager_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Google Tag Manager Container ID
                </label>
                <div class="mt-1">
                  <Input
                    id="google_tag_manager_id"
                    v-model="form.google_tag_manager_id"
                    type="text"
                    placeholder="GTM-XXXXXXX"
                    class="block w-full"
                    :class="{ 'border-red-300': form.errors.google_tag_manager_id }"
                  />
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                  Your Google Tag Manager Container ID (starts with GTM-).
                </p>
                <div v-if="form.errors.google_tag_manager_id" class="mt-2 text-sm text-red-600">
                  {{ form.errors.google_tag_manager_id }}
                </div>
              </div>

              <!-- Facebook Pixel -->
              <div class="mb-6">
                <label for="facebook_pixel_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Facebook Pixel ID
                </label>
                <div class="mt-1">
                  <Input
                    id="facebook_pixel_id"
                    v-model="form.facebook_pixel_id"
                    type="text"
                    placeholder="123456789012345"
                    class="block w-full"
                    :class="{ 'border-red-300': form.errors.facebook_pixel_id }"
                  />
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                  Your Facebook Pixel ID (numeric ID).
                </p>
                <div v-if="form.errors.facebook_pixel_id" class="mt-2 text-sm text-red-600">
                  {{ form.errors.facebook_pixel_id }}
                </div>
              </div>

              <!-- Cookie Consent -->
              <div>
                <div class="flex items-center">
                  <input
                    id="cookie_consent_enabled"
                    v-model="form.cookie_consent_enabled"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="cookie_consent_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Require cookie consent for analytics
                  </label>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                  Show cookie consent banner before loading analytics scripts (GDPR compliance).
                </p>
                <div v-if="form.errors.cookie_consent_enabled" class="mt-2 text-sm text-red-600">
                  {{ form.errors.cookie_consent_enabled }}
                </div>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-right border-t border-gray-200 dark:border-gray-600">
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <Icon v-if="form.processing" icon="line-md:loading-loop" class="animate-spin -ml-1 mr-2 h-4 w-4" />
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
