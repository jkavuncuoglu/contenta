<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

interface Props {
  config: {
    title?: string;
    description?: string;
    submitUrl?: string;
    successMessage?: string;
    showPhone?: boolean;
    showSubject?: boolean;
    backgroundColor?: string;
  };
}

const props = withDefaults(defineProps<Props>(), {
  config: () => ({
    title: 'Get in Touch',
    description: 'Fill out the form below and we\'ll get back to you as soon as possible.',
    submitUrl: '/contact',
    successMessage: 'Thank you for your message! We\'ll be in touch soon.',
    showPhone: true,
    showSubject: true,
    backgroundColor: 'bg-gray-50 dark:bg-gray-800',
  }),
});

const form = useForm({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
});

const showSuccess = ref(false);

const submit = () => {
  form.post(props.config.submitUrl || '/contact', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      showSuccess.value = true;
      setTimeout(() => {
        showSuccess.value = false;
      }, 5000);
    },
  });
};
</script>

<template>
  <section class="py-16" :class="config.backgroundColor">
    <div class="container mx-auto px-4">
      <div class="max-w-3xl mx-auto">
        <div class="text-center mb-10">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
            {{ config.title }}
          </h2>
          <p class="text-lg text-gray-600 dark:text-gray-300">
            {{ config.description }}
          </p>
        </div>

        <!-- Success Message -->
        <div
          v-if="showSuccess"
          class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg flex items-start gap-3"
        >
          <Icon icon="ph:check-circle" class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" />
          <p class="text-green-800 dark:text-green-200">
            {{ config.successMessage }}
          </p>
        </div>

        <form @submit.prevent="submit" class="bg-white dark:bg-gray-900 p-8 rounded-xl shadow-lg space-y-6">
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Name *
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              placeholder="Your name"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.name }}
            </p>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Email *
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              placeholder="your@email.com"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Phone (optional) -->
          <div v-if="config.showPhone">
            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Phone
            </label>
            <input
              id="phone"
              v-model="form.phone"
              type="tel"
              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              placeholder="+1 (555) 000-0000"
            />
            <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.phone }}
            </p>
          </div>

          <!-- Subject (optional) -->
          <div v-if="config.showSubject">
            <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Subject
            </label>
            <input
              id="subject"
              v-model="form.subject"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              placeholder="How can we help?"
            />
            <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.subject }}
            </p>
          </div>

          <!-- Message -->
          <div>
            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Message *
            </label>
            <textarea
              id="message"
              v-model="form.message"
              required
              rows="5"
              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-800 text-gray-900 dark:text-white resize-none"
              placeholder="Tell us more about your inquiry..."
            ></textarea>
            <p v-if="form.errors.message" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.message }}
            </p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <span v-if="form.processing">Sending...</span>
            <span v-else>Send Message</span>
            <Icon v-if="!form.processing" icon="ph:paper-plane-right" class="w-5 h-5" />
          </button>
        </form>
      </div>
    </div>
  </section>
</template>
