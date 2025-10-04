<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="text-center py-12">
      <p class="text-red-600 dark:text-red-400">{{ error }}</p>
      <button @click="fetchPost" class="mt-2 text-blue-600 dark:text-blue-400 hover:text-blue-500">
        Try again
      </button>
    </div>

    <!-- Edit form -->
    <div v-else-if="currentPost">
      <!-- Page header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Post</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Update your blog post or article
          </p>
        </div>
        <div class="flex items-center space-x-3">
          <Link
            href="/admin/posts"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Back to Posts
          </Link>
        </div>
      </div>

      <!-- TODO: Implement edit form similar to Create.vue but pre-populated with currentPost data -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <p class="text-gray-500 dark:text-gray-400">
          Edit form will be implemented here. Currently editing: {{ currentPost.title }}
        </p>
      </div>
    </div>

    <!-- Post not found -->
    <div v-else class="text-center py-12">
      <p class="text-gray-500 dark:text-gray-400">Post not found</p>
      <Link
        href="/admin/posts"
        class="mt-2 inline-block text-blue-600 dark:text-blue-400 hover:text-blue-500"
      >
        Back to Posts
      </Link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { usePostsStore } from '@/stores/posts';

interface Props {
  id: number | string;
}

defineProps<Props>();

const postsStore = usePostsStore();
const { currentPost, loading, error } = postsStore;

const fetchPost = async () => {
  const postId = Number(({} as any).id ?? undefined);
  // prefer the prop passed by Inertia; fallback to currentPost.id
  const propId = (typeof ({} as any).id !== 'undefined') ? Number(({} as any).id) : undefined;
  const id = propId ?? currentPost?.id;
  if (id) {
    await postsStore.fetchPost(Number(id));
  }
};

onMounted(() => {
  fetchPost();
});
</script>
