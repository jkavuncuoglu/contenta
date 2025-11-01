<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
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
  recaptchaVersions: { [key: string]: string };
  honeypotFieldNames: { [key: string]: string };
  minimumTimes: { [key: string]: string };
}

interface Props {
  settings: Settings;
  options: Options;
}

const props = defineProps<Props>();

const page = usePage();
const flashMessage = computed(() => page.props.flash as { success?: string; error?: string } | null);

const form = useForm({
  // General security
  security_enabled: props.settings.security_enabled?.value || true,
  security_rate_limiting: props.settings.security_rate_limiting?.value || true,
  security_csrf_protection: props.settings.security_csrf_protection?.value || true,
  security_ip_blocking: props.settings.security_ip_blocking?.value || false,

  // Honeypot settings
  honeypot_enabled: props.settings.honeypot_enabled?.value || false,
  honeypot_field_name: props.settings.honeypot_field_name?.value || 'hp_field',
  honeypot_minimum_time: props.settings.honeypot_minimum_time?.value || 3,
  honeypot_timer_enabled: props.settings.honeypot_timer_enabled?.value || true,
  honeypot_input_field: props.settings.honeypot_input_field?.value || 'website',

  // reCAPTCHA settings
  recaptcha_enabled: props.settings.recaptcha_enabled?.value || false,
  recaptcha_site_key: props.settings.recaptcha_site_key?.value || '',
  recaptcha_secret_key: props.settings.recaptcha_secret_key?.value || '',
  recaptcha_version: props.settings.recaptcha_version?.value || 'v2',
  recaptcha_threshold: props.settings.recaptcha_threshold?.value || 0.5,
});

const submit = () => {
  form.put('/admin/settings/security', {
    preserveScroll: true,
  });
};

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Settings',
  },
  {
    title: 'Security',
  },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Security Settings" />

    <div class="space-y-6">
      <HeadingSmall
        title="Security Settings"
        description="Configure security features to protect your site from spam and attacks"
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
          <div class="px-6 py-6 space-y-8">
            <!-- General Security -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">General Security</h3>

              <div class="space-y-4">
                <div class="flex items-center">
                  <input
                    id="security_enabled"
                    v-model="form.security_enabled"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="security_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Enable security features
                  </label>
                </div>

                <div class="flex items-center">
                  <input
                    id="security_rate_limiting"
                    v-model="form.security_rate_limiting"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="security_rate_limiting" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Enable rate limiting
                  </label>
                </div>

                <div class="flex items-center">
                  <input
                    id="security_csrf_protection"
                    v-model="form.security_csrf_protection"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="security_csrf_protection" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Enable CSRF protection
                  </label>
                </div>

                <div class="flex items-center">
                  <input
                    id="security_ip_blocking"
                    v-model="form.security_ip_blocking"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="security_ip_blocking" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Enable IP blocking for suspicious activity
                  </label>
                </div>
              </div>
            </div>

            <!-- Honeypot Protection -->
            <div class="border-t border-gray-200 dark:border-gray-600 pt-8">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Honeypot Protection</h3>

              <div class="space-y-6">
                <div class="flex items-center">
                  <input
                    id="honeypot_enabled"
                    v-model="form.honeypot_enabled"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="honeypot_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Enable honeypot protection
                  </label>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Honeypot protection adds invisible fields to forms to catch spam bots.
                </p>

                <div v-if="form.honeypot_enabled" class="pl-6 space-y-6">
                  <!-- Honeypot Field Name -->
                  <div>
                    <label for="honeypot_field_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Honeypot Field Name
                    </label>
                    <div class="mt-1">
                      <select
                        id="honeypot_field_name"
                        v-model="form.honeypot_field_name"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-300': form.errors.honeypot_field_name }"
                      >
                        <option v-for="(label, value) in options.honeypotFieldNames" :key="value" :value="value">
                          {{ label }}
                        </option>
                      </select>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                      The name attribute for the honeypot field.
                    </p>
                    <div v-if="form.errors.honeypot_field_name" class="mt-2 text-sm text-red-600">
                      {{ form.errors.honeypot_field_name }}
                    </div>
                  </div>

                  <!-- Honeypot Input Field -->
                  <div>
                    <label for="honeypot_input_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Hidden Input Field Name
                    </label>
                    <div class="mt-1">
                      <Input
                        id="honeypot_input_field"
                        v-model="form.honeypot_input_field"
                        type="text"
                        class="block w-full"
                        :class="{ 'border-red-300': form.errors.honeypot_input_field }"
                      />
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                      The name for the hidden input field that bots shouldn't fill.
                    </p>
                    <div v-if="form.errors.honeypot_input_field" class="mt-2 text-sm text-red-600">
                      {{ form.errors.honeypot_input_field }}
                    </div>
                  </div>

                  <!-- Timer Settings -->
                  <div class="flex items-center">
                    <input
                      id="honeypot_timer_enabled"
                      v-model="form.honeypot_timer_enabled"
                      type="checkbox"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label for="honeypot_timer_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                      Enable minimum time validation
                    </label>
                  </div>

                  <div v-if="form.honeypot_timer_enabled">
                    <label for="honeypot_minimum_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Minimum Time Before Submission
                    </label>
                    <div class="mt-1">
                      <select
                        id="honeypot_minimum_time"
                        v-model="form.honeypot_minimum_time"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-300': form.errors.honeypot_minimum_time }"
                      >
                        <option v-for="(label, value) in options.minimumTimes" :key="value" :value="parseInt(value)">
                          {{ label }}
                        </option>
                      </select>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                      Minimum time users must wait before submitting forms.
                    </p>
                    <div v-if="form.errors.honeypot_minimum_time" class="mt-2 text-sm text-red-600">
                      {{ form.errors.honeypot_minimum_time }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- reCAPTCHA Protection -->
            <div class="border-t border-gray-200 dark:border-gray-600 pt-8">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">reCAPTCHA Protection</h3>

              <div class="space-y-6">
                <div class="flex items-center">
                  <input
                    id="recaptcha_enabled"
                    v-model="form.recaptcha_enabled"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="recaptcha_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Enable reCAPTCHA protection
                  </label>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  reCAPTCHA helps protect your forms from automated spam and abuse.
                </p>

                <div v-if="form.recaptcha_enabled" class="pl-6 space-y-6">
                  <!-- reCAPTCHA Version -->
                  <div>
                    <label for="recaptcha_version" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      reCAPTCHA Version
                    </label>
                    <div class="mt-1">
                      <select
                        id="recaptcha_version"
                        v-model="form.recaptcha_version"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-300': form.errors.recaptcha_version }"
                      >
                        <option v-for="(label, value) in options.recaptchaVersions" :key="value" :value="value">
                          {{ label }}
                        </option>
                      </select>
                    </div>
                    <div v-if="form.errors.recaptcha_version" class="mt-2 text-sm text-red-600">
                      {{ form.errors.recaptcha_version }}
                    </div>
                  </div>

                  <!-- Site Key -->
                  <div>
                    <label for="recaptcha_site_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Site Key
                    </label>
                    <div class="mt-1">
                      <Input
                        id="recaptcha_site_key"
                        v-model="form.recaptcha_site_key"
                        type="text"
                        placeholder="6Lc..."
                        class="block w-full"
                        :class="{ 'border-red-300': form.errors.recaptcha_site_key }"
                      />
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                      Your reCAPTCHA site key from Google reCAPTCHA console.
                    </p>
                    <div v-if="form.errors.recaptcha_site_key" class="mt-2 text-sm text-red-600">
                      {{ form.errors.recaptcha_site_key }}
                    </div>
                  </div>

                  <!-- Secret Key -->
                  <div>
                    <label for="recaptcha_secret_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Secret Key
                    </label>
                    <div class="mt-1">
                      <Input
                        id="recaptcha_secret_key"
                        v-model="form.recaptcha_secret_key"
                        type="password"
                        placeholder="6Lc..."
                        class="block w-full"
                        :class="{ 'border-red-300': form.errors.recaptcha_secret_key }"
                      />
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                      Your reCAPTCHA secret key from Google reCAPTCHA console.
                    </p>
                    <div v-if="form.errors.recaptcha_secret_key" class="mt-2 text-sm text-red-600">
                      {{ form.errors.recaptcha_secret_key }}
                    </div>
                  </div>

                  <!-- Threshold for v3 -->
                  <div v-if="form.recaptcha_version === 'v3'">
                    <label for="recaptcha_threshold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Score Threshold (v3 only)
                    </label>
                    <div class="mt-1">
                      <Input
                        id="recaptcha_threshold"
                        v-model="form.recaptcha_threshold"
                        type="number"
                        step="0.1"
                        min="0"
                        max="1"
                        class="block w-full"
                        :class="{ 'border-red-300': form.errors.recaptcha_threshold }"
                      />
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                      Minimum score required (0.0 - 1.0). Higher values are more restrictive.
                    </p>
                    <div v-if="form.errors.recaptcha_threshold" class="mt-2 text-sm text-red-600">
                      {{ form.errors.recaptcha_threshold }}
                    </div>
                  </div>
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
              Save Security Settings
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
