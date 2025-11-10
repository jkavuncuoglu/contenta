<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Icon } from '@iconify/vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';

interface ThemeColors {
  id?: number;
  name?: string;
  light_primary: string;
  light_secondary: string;
  light_accent: string;
  light_background: string;
  light_surface: string;
  light_text: string;
  light_text_secondary: string;
  dark_primary: string;
  dark_secondary: string;
  dark_accent: string;
  dark_background: string;
  dark_surface: string;
  dark_text: string;
  dark_text_secondary: string;
}

interface Props {
  theme?: ThemeColors;
}

const props = defineProps<Props>();

const activeTab = ref<'light' | 'dark'>('light');

const form = useForm({
  light_primary: props.theme?.light_primary || '#3B82F6',
  light_secondary: props.theme?.light_secondary || '#8B5CF6',
  light_accent: props.theme?.light_accent || '#10B981',
  light_background: props.theme?.light_background || '#FFFFFF',
  light_surface: props.theme?.light_surface || '#F9FAFB',
  light_text: props.theme?.light_text || '#1F2937',
  light_text_secondary: props.theme?.light_text_secondary || '#6B7280',
  dark_primary: props.theme?.dark_primary || '#60A5FA',
  dark_secondary: props.theme?.dark_secondary || '#A78BFA',
  dark_accent: props.theme?.dark_accent || '#34D399',
  dark_background: props.theme?.dark_background || '#111827',
  dark_surface: props.theme?.dark_surface || '#1F2937',
  dark_text: props.theme?.dark_text || '#F9FAFB',
  dark_text_secondary: props.theme?.dark_text_secondary || '#D1D5DB',
});

const submit = () => {
  form.put('/admin/settings/theme', {
    preserveScroll: true,
    onSuccess: () => {
      // Refresh to apply new theme
      window.location.reload();
    },
  });
};

const resetToDefaults = () => {
  if (confirm('Are you sure you want to reset to default colors?')) {
    form.light_primary = '#3B82F6';
    form.light_secondary = '#8B5CF6';
    form.light_accent = '#10B981';
    form.light_background = '#FFFFFF';
    form.light_surface = '#F9FAFB';
    form.light_text = '#1F2937';
    form.light_text_secondary = '#6B7280';
    form.dark_primary = '#60A5FA';
    form.dark_secondary = '#A78BFA';
    form.dark_accent = '#34D399';
    form.dark_background = '#111827';
    form.dark_surface = '#1F2937';
    form.dark_text = '#F9FAFB';
    form.dark_text_secondary = '#D1D5DB';
  }
};

const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'Settings' },
  { title: 'Theme Colors' },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Theme Settings" />

    <div class="space-y-6">
      <HeadingSmall
        title="Theme Color Settings"
        description="Customize the color scheme for light and dark modes"
      />

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Theme Mode Tabs -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
          <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px">
              <button
                type="button"
                @click="activeTab = 'light'"
                :class="[
                  'flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                  activeTab === 'light'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                ]"
              >
                <Icon icon="ph:sun" class="w-5 h-5" />
                Light Mode
              </button>
              <button
                type="button"
                @click="activeTab = 'dark'"
                :class="[
                  'flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                  activeTab === 'dark'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                ]"
              >
                <Icon icon="ph:moon" class="w-5 h-5" />
                Dark Mode
              </button>
            </nav>
          </div>

          <!-- Light Theme Colors -->
          <div v-show="activeTab === 'light'" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <!-- Primary Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Primary Color
                </label>
                <div class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form.light_primary"
                    class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form.light_primary"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white uppercase font-mono text-sm"
                    placeholder="#3B82F6"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Main brand color for buttons, links</p>
              </div>

              <!-- Secondary Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Secondary Color
                </label>
                <div class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form.light_secondary"
                    class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form.light_secondary"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white uppercase font-mono text-sm"
                    placeholder="#8B5CF6"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Accent highlights and badges</p>
              </div>

              <!-- Accent Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Accent Color
                </label>
                <div class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form.light_accent"
                    class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form.light_accent"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white uppercase font-mono text-sm"
                    placeholder="#10B981"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Success states and CTAs</p>
              </div>

              <!-- Background Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Background Color
                </label>
                <div class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form.light_background"
                    class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form.light_background"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white uppercase font-mono text-sm"
                    placeholder="#FFFFFF"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Main page background</p>
              </div>

              <!-- Surface Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Surface Color
                </label>
                <div class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form.light_surface"
                    class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form.light_surface"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white uppercase font-mono text-sm"
                    placeholder="#F9FAFB"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cards and panels</p>
              </div>

              <!-- Text Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Text Color
                </label>
                <div class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form.light_text"
                    class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form.light_text"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white uppercase font-mono text-sm"
                    placeholder="#1F2937"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Primary text color</p>
              </div>

              <!-- Secondary Text Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Secondary Text
                </label>
                <div class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form.light_text_secondary"
                    class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form.light_text_secondary"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white uppercase font-mono text-sm"
                    placeholder="#6B7280"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Muted text and labels</p>
              </div>
            </div>
          </div>

          <!-- Dark Theme Colors -->
          <div v-show="activeTab === 'dark'" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <!-- Primary Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Primary Color
                </label>
                <div class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form.dark_primary"
                    class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form.dark_primary"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white uppercase font-mono text-sm"
                    placeholder="#60A5FA"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Main brand color for buttons, links</p>
              </div>

              <!-- Secondary Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Secondary Color
                </label>
                <div class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form.dark_secondary"
                    class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form.dark_secondary"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white uppercase font-mono text-sm"
                    placeholder="#A78BFA"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Accent highlights and badges</p>
              </div>

              <!-- Accent Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Accent Color
                </label>
                <div class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form.dark_accent"
                    class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form.dark_accent"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white uppercase font-mono text-sm"
                    placeholder="#34D399"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Success states and CTAs</p>
              </div>

              <!-- Background Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Background Color
                </label>
                <div class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form.dark_background"
                    class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form.dark_background"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white uppercase font-mono text-sm"
                    placeholder="#111827"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Main page background</p>
              </div>

              <!-- Surface Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Surface Color
                </label>
                <div class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form.dark_surface"
                    class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form.dark_surface"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white uppercase font-mono text-sm"
                    placeholder="#1F2937"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cards and panels</p>
              </div>

              <!-- Text Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Text Color
                </label>
                <div class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form.dark_text"
                    class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form.dark_text"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white uppercase font-mono text-sm"
                    placeholder="#F9FAFB"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Primary text color</p>
              </div>

              <!-- Secondary Text Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Secondary Text
                </label>
                <div class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form.dark_text_secondary"
                    class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form.dark_text_secondary"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white uppercase font-mono text-sm"
                    placeholder="#D1D5DB"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Muted text and labels</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
          <button
            type="button"
            @click="resetToDefaults"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            Reset to Defaults
          </button>

          <div class="flex items-center gap-3">
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
            >
              <Icon v-if="form.processing" icon="svg-spinners:ring-resize" class="w-4 h-4 mr-2" />
              <Icon v-else icon="ph:floppy-disk" class="w-4 h-4 mr-2" />
              {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </div>

        <!-- Preview Info -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
          <div class="flex">
            <Icon icon="ph:info" class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5" />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">
                Preview Your Changes
              </h3>
              <p class="mt-1 text-sm text-blue-700 dark:text-blue-400">
                After saving, the page will refresh to apply your new theme colors across the entire site.
                Changes will be visible in both light and dark modes.
              </p>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
