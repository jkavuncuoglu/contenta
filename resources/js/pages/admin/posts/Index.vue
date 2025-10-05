<template>
  <Head title="Posts" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Page header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Posts</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Manage your blog posts and articles
          </p>
        </div>
        <Link
          href="/admin/posts/create"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <Icon name="plus" class="w-4 h-4 mr-2" />
          New Post
        </Link>
      </div>

      <!-- Posts table -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div v-if="posts.length === 0" class="p-6 text-center">
          <Icon name="document-text" class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No posts</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Get started by creating your first post.
          </p>
          <div class="mt-6">
            <Link
              href="/admin/posts/create"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <Icon name="plus" class="w-4 h-4 mr-2" />
              New Post
            </Link>
          </div>
        </div>

        <div v-else>
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Title
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Author
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Date
                </th>
                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">Actions</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="post in posts" :key="post.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ post.title }}
                      </p>
                      <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                        {{ post.excerpt || 'No excerpt' }}
                      </p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">{{ post.author?.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusClass(post.status)">
                    {{ post.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ formatDate(post.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center space-x-2">
                    <Link
                      :href="`/admin/posts/${post.id}/edit`"
                      class="text-blue-600 dark:text-blue-400 hover:text-blue-500"
                    >
                      Edit
                    </Link>
                    <button
                      v-if="post.status === 'published'"
                      @click="updatePostStatus(post.id, 'draft')"
                      class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-500"
                    >
                      Unpublish
                    </button>
                    <button
                      v-else-if="post.status === 'draft'"
                      @click="updatePostStatus(post.id, 'published')"
                      class="text-green-600 dark:text-green-400 hover:text-green-500"
                    >
                      Publish
                    </button>
                    <button
                      @click="deletePost(post)"
                      class="text-red-600 dark:text-red-400 hover:text-red-500"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import Icon from '@/components/Icon.vue';
import { computed } from 'vue';

interface Post {
  id: number;
  title: string;
  excerpt?: string;
  status: string;
  created_at: string;
  author?: {
    name: string;
  };
}

interface Props {
  posts?: Post[]; // make optional because Inertia may provide via page props
}

const props = defineProps<Props>();

// Fallback to Inertia page props if the component wasn't passed `posts` directly
const page = usePage();
const posts = computed<Post[]>(() => {
  // page.props may contain posts under different keys; try common locations
  const p = (page.props as any)?.value ?? (page as any)?.props ?? {};
  const fromPage = p.posts ?? p.data?.posts ?? p.postList ?? undefined;
  return props.posts ?? fromPage ?? [];
});

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Posts',
    href: '/admin/posts',
  },
];

const updatePostStatus = (postId: number, status: string) => {
  router.patch(`/admin/posts/${postId}`, { status });
};

const deletePost = (post: Post) => {
  if (confirm(`Are you sure you want to delete "${post.title}"?`)) {
    router.delete(`/admin/posts/${post.id}`);
  }
};

const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    published: 'bg-green-100 text-green-800',
    draft: 'bg-yellow-100 text-yellow-800',
    private: 'bg-gray-100 text-gray-800',
    scheduled: 'bg-blue-100 text-blue-800',
    trash: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString();
};
</script>
