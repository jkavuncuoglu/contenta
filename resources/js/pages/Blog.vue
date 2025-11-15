<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

interface Author {
  id: number;
  name: string;
  email: string;
}

interface PostType {
  id: number;
  name: string;
  slug: string;
}

interface Post {
  id: number;
  title: string;
  slug: string;
  excerpt: string;
  content_html: string;
  published_at: string;
  author: Author;
  post_type: PostType;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedPosts {
  data: Post[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  links: PaginationLink[];
}

interface Props {
  posts: PaginatedPosts;
  siteTitle: string;
  siteTagline: string;
}

defineProps<Props>();

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <Head :title="`${siteTitle} - Blog`" />

    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="text-center">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ siteTitle }}
          </h1>
          <p v-if="siteTagline" class="mt-2 text-lg text-gray-600 dark:text-gray-400">
            {{ siteTagline }}
          </p>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Blog Posts -->
        <div class="lg:col-span-2">
          <div v-if="posts.data.length === 0" class="text-center py-12">
            <Icon icon="material-symbols-light:article-outline" class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No posts yet</h3>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Check back later for new content!</p>
          </div>

          <div v-else class="space-y-8">
            <article
              v-for="post in posts.data"
              :key="post.id"
              class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden"
            >
              <div class="p-6">
                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-4">
                  <time :datetime="post.published_at">
                    {{ formatDate(post.published_at) }}
                  </time>
                  <span class="mx-2">•</span>
                  <span>{{ post.author.name }}</span>
                  <span v-if="post.post_type" class="mx-2">•</span>
                  <span v-if="post.post_type" class="capitalize">{{ post.post_type.name }}</span>
                </div>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                  <a :href="`/blog/${post.slug}`" class="hover:text-blue-600 dark:hover:text-blue-400">
                    {{ post.title }}
                  </a>
                </h2>

                <p v-if="post.excerpt" class="text-gray-600 dark:text-gray-300 mb-4">
                  {{ post.excerpt }}
                </p>

                <div v-else-if="post.content_html" class="text-gray-600 dark:text-gray-300 mb-4">
                  <div v-html="post.content_html.substring(0, 300) + '...'" class="prose prose-sm max-w-none"></div>
                </div>

                <a
                  :href="`/blog/${post.slug}`"
                  class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                >
                  Read more
                  <Icon icon="material-symbols-light:arrow-forward" class="ml-1 h-4 w-4" />
                </a>
              </div>
            </article>
          </div>

          <!-- Pagination -->
          <div v-if="posts.links && posts.links.length > 3" class="mt-8">
            <nav class="flex justify-center">
              <div class="flex space-x-1">
                <a
                  v-for="link in posts.links"
                  :key="link.label"
                  :href="link.url || '#'"
                  :class="[
                    'px-3 py-2 text-sm rounded-md',
                    link.active
                      ? 'bg-blue-600 text-white'
                      : link.url
                      ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
                      : 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed'
                  ]"
                  :disabled="!link.url"
                  v-html="link.label"
                ></a>
              </div>
            </nav>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">About</h3>
            <p class="text-gray-600 dark:text-gray-400">
              Welcome to our blog! Here you'll find the latest posts and updates.
            </p>
          </div>

          <!-- Recent Posts Widget -->
          <div v-if="posts.data.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Posts</h3>
            <div class="space-y-3">
              <div
                v-for="post in posts.data.slice(0, 5)"
                :key="post.id"
                class="border-b border-gray-200 dark:border-gray-700 pb-3 last:border-b-0 last:pb-0"
              >
                <a
                  :href="`/blog/${post.slug}`"
                  class="block text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                >
                  {{ post.title }}
                </a>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ formatDate(post.published_at) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>