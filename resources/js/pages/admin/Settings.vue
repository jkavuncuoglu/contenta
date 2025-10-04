<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Site Settings</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Configure your website's general settings and preferences.
        </p>
      </div>
      <button
        @click="saveSettings"
        :disabled="loading"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
      >
        {{ loading ? 'Saving...' : 'Save Changes' }}
      </button>
    </div>

    <!-- Settings Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700">
      <nav class="-mb-px flex space-x-8">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            activeTab === tab.id
              ? 'border-blue-500 text-blue-600 dark:text-blue-400'
              : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300',
            'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          {{ tab.name }}
        </button>
      </nav>
    </div>

    <!-- General Settings -->
    <div v-show="activeTab === 'general'" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Site Information</h3>
        <div class="space-y-4">
          <div>
            <label for="site-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Site Name
            </label>
            <input
              id="site-name"
              v-model="settings.site_name"
              type="text"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
              placeholder="Your Site Name"
            />
          </div>

          <div>
            <label for="site-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Site Description
            </label>
            <textarea
              id="site-description"
              v-model="settings.site_description"
              rows="3"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
              placeholder="A brief description of your website"
            ></textarea>
          </div>

          <div>
            <label for="site-url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Site URL
            </label>
            <input
              id="site-url"
              v-model="settings.site_url"
              type="url"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
              placeholder="https://yoursite.com"
            />
          </div>

          <div>
            <label for="admin-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Admin Email
            </label>
            <input
              id="admin-email"
              v-model="settings.admin_email"
              type="email"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
              placeholder="admin@yoursite.com"
            />
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Regional Settings</h3>
        <div class="space-y-4">
          <div>
            <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Timezone
            </label>
            <select
              id="timezone"
              v-model="settings.timezone"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
            >
              <option value="UTC">UTC</option>
              <option value="America/New_York">Eastern Time (ET)</option>
              <option value="America/Chicago">Central Time (CT)</option>
              <option value="America/Denver">Mountain Time (MT)</option>
              <option value="America/Los_Angeles">Pacific Time (PT)</option>
              <option value="Europe/London">London</option>
              <option value="Europe/Paris">Paris</option>
              <option value="Asia/Tokyo">Tokyo</option>
            </select>
          </div>

          <div>
            <label for="date-format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Date Format
            </label>
            <select
              id="date-format"
              v-model="settings.date_format"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
            >
              <option value="Y-m-d">2024-01-15</option>
              <option value="m/d/Y">01/15/2024</option>
              <option value="d/m/Y">15/01/2024</option>
              <option value="F j, Y">January 15, 2024</option>
              <option value="j F Y">15 January 2024</option>
            </select>
          </div>

          <div>
            <label for="time-format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Time Format
            </label>
            <select
              id="time-format"
              v-model="settings.time_format"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
            >
              <option value="H:i">24-hour (14:30)</option>
              <option value="g:i A">12-hour (2:30 PM)</option>
            </select>
          </div>

          <div>
            <label for="posts-per-page" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Posts per Page
            </label>
            <input
              id="posts-per-page"
              v-model.number="settings.posts_per_page"
              type="number"
              min="1"
              max="100"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Content Settings -->
    <div v-show="activeTab === 'content'" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Content Management</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                User Registration
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400">Allow new users to register accounts</p>
            </div>
            <button
              @click="settings.registration_enabled = !settings.registration_enabled"
              :class="[
                settings.registration_enabled ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700',
                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2'
              ]"
            >
              <span
                :class="[
                  settings.registration_enabled ? 'translate-x-5' : 'translate-x-0',
                  'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                ]"
              />
            </button>
          </div>

          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Comment Moderation
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400">Require approval for new comments</p>
            </div>
            <button
              @click="settings.moderation_enabled = !settings.moderation_enabled"
              :class="[
                settings.moderation_enabled ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700',
                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2'
              ]"
            >
              <span
                :class="[
                  settings.moderation_enabled ? 'translate-x-5' : 'translate-x-0',
                  'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                ]"
              />
            </button>
          </div>

          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Maintenance Mode
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400">Put site in maintenance mode</p>
            </div>
            <button
              @click="settings.maintenance_mode = !settings.maintenance_mode"
              :class="[
                settings.maintenance_mode ? 'bg-red-600' : 'bg-gray-200 dark:bg-gray-700',
                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2'
              ]"
            >
              <span
                :class="[
                  settings.maintenance_mode ? 'translate-x-5' : 'translate-x-0',
                  'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                ]"
              />
            </button>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">SEO Settings</h3>
        <div class="space-y-4">
          <div>
            <label for="meta-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Default Meta Title
            </label>
            <input
              id="meta-title"
              v-model="settings.meta_title"
              type="text"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
              placeholder="Your Site - Default Title"
            />
          </div>

          <div>
            <label for="meta-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Default Meta Description
            </label>
            <textarea
              id="meta-description"
              v-model="settings.meta_description"
              rows="3"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
              placeholder="Default description for search engines"
            ></textarea>
          </div>

          <div>
            <label for="meta-keywords" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Default Meta Keywords
            </label>
            <input
              id="meta-keywords"
              v-model="settings.meta_keywords"
              type="text"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
              placeholder="keyword1, keyword2, keyword3"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const loading = ref(false);
const activeTab = ref('general');

const tabs = [
  { id: 'general', name: 'General' },
  { id: 'appearance', name: 'Appearance' },
  { id: 'content', name: 'Content' },
  { id: 'advanced', name: 'Advanced' },
];

const settings = ref<SystemSettings & {
  social_links: Record<string, string>;
  meta_title: string;
  meta_description: string;
  meta_keywords: string;
  mail_driver: string;
  mail_from_address: string;
  mail_from_name: string;
}>({
  site_name: 'NovemberCMS',
  site_description: 'A modern content management system',
  site_url: 'https://localhost:8000',
  admin_email: 'admin@localhost',
  timezone: 'UTC',
  date_format: 'Y-m-d',
  time_format: 'H:i',
  posts_per_page: 10,
  registration_enabled: true,
  moderation_enabled: false,
  maintenance_mode: false,
  social_links: {
    facebook: '',
    x: '',
    instagram: '',
    linkedin: '',
    youtube: '',
    github: ''
  },
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
  mail_driver: 'smtp',
  mail_from_address: '',
  mail_from_name: ''
});

const fetchSettings = async () => {
  try {
    loading.value = true;
    router.get('/admin/settings', {}, {
      preserveState: true,
      onSuccess: (page) => {
        // Try common prop locations
        const data = (page.props && (page.props.settings ?? page.props.data)) ?? page.props ?? {};
        settings.value = { ...settings.value, ...data };
      },
      onError: (page) => {
        console.error('Failed to fetch settings (Inertia):', page);
      }
    });
  } catch (error) {
    console.error('Failed to fetch settings:', error);
  } finally {
    loading.value = false;
  }
};

const saveSettings = async () => {
  try {
    loading.value = true;
    router.put('/admin/settings', settings.value, {
      preserveState: true,
      onSuccess: () => {
        console.log('Settings saved successfully');
      },
      onError: (page) => {
        console.error('Failed to save settings (Inertia):', page);
      }
    });
  } catch (error) {
    console.error('Failed to save settings:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchSettings();
});
</script>
