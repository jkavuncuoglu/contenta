<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageLayout from '@/layouts/PageLayout.vue';
</script>

<template>
  <PageLayout>
    <Head title="Welcome" />

    <div class="bg-gradient-to-b from-blue-50 to-white dark:from-gray-900 dark:to-gray-800">
      <!-- Hero Section -->
      <div class="container mx-auto px-4 py-20 lg:py-32">
        <div class="max-w-4xl mx-auto text-center">
          <h1 class="text-5xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-6">
            Welcome to <span class="text-blue-600 dark:text-blue-400">Contenta</span>
          </h1>
          <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
            Professional content management made simple. Create, manage, and publish your content with ease.
          </p>
          <div class="flex items-center justify-center gap-4 flex-wrap">
            <a
              href="/blog"
              class="px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors"
            >
              Explore Blog
            </a>
            <a
              v-if="!$page.props.auth.user"
              href="/register"
              class="px-8 py-3 border-2 border-blue-600 text-blue-600 dark:text-blue-400 rounded-lg font-semibold hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
            >
              Get Started
            </a>
          </div>
        </div>
      </div>

      <!-- Features Section -->
      <div class="container mx-auto px-4 py-16 pb-24">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
          <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Easy Content Management</h3>
            <p class="text-gray-600 dark:text-gray-400">Create and manage your content with an intuitive interface designed for efficiency.</p>
          </div>

          <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Customizable Design</h3>
            <p class="text-gray-600 dark:text-gray-400">Tailor your site's appearance with our powerful theme customization tools.</p>
          </div>

          <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Lightning Fast</h3>
            <p class="text-gray-600 dark:text-gray-400">Built with modern technologies for optimal performance and user experience.</p>
          </div>
        </div>
      </div>
    </div>
  </PageLayout>
</template>
