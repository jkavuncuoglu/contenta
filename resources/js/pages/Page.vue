<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface Page {
  id: number;
  title: string;
  slug: string;
  content_html: string;
  meta_title?: string;
  meta_description?: string;
  published_at: string;
}

interface Props {
  page: Page;
  siteTitle: string;
}

const props = defineProps<Props>();

const pageTitle = props.page.meta_title || props.page.title;
const fullTitle = `${pageTitle} - ${props.siteTitle}`;
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <Head :title="fullTitle">
      <meta v-if="page.meta_description" name="description" :content="page.meta_description" />
    </Head>

    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="text-center">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ page.title }}
          </h1>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <article class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="p-8">
          <div
            v-html="page.content_html"
            class="prose prose-lg max-w-none dark:prose-invert prose-headings:text-gray-900 dark:prose-headings:text-white prose-p:text-gray-700 dark:prose-p:text-gray-300"
          ></div>
        </div>
      </article>
    </main>
  </div>
</template>