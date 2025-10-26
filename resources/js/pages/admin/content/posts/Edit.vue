<template>
    <Head title="Edit Post" />
    <AppLayout>
        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Loading state -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <div
                    class="h-8 w-8 animate-spin rounded-full border-b-2 border-blue-500"
                ></div>
            </div>

            <!-- Error state -->
            <div v-else-if="error" class="py-12 text-center">
                <p class="text-red-600 dark:text-red-400">{{ error }}</p>
                <button
                    @click="fetchPost"
                    class="mt-2 text-blue-600 hover:text-blue-500 dark:text-blue-400"
                >
                    Try again
                </button>
            </div>

            <!-- Edit form -->
            <div v-else-if="currentPost">
                <!-- Page header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1
                            class="text-2xl font-semibold text-gray-900 dark:text-white"
                        >
                            Edit Post
                        </h1>
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Update your blog post or article
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <Link
                            href="/admin/posts"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Back to Posts
                        </Link>
                    </div>
                </div>

                <!-- TODO: Implement edit form similar to Create.vue but pre-populated with currentPost data -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <p class="text-gray-500 dark:text-gray-400">
                        Edit form will be implemented here. Currently editing:
                        {{ currentPost.title }}
                    </p>
                </div>
            </div>

            <!-- Post not found -->
            <div v-else class="py-12 text-center">
                <p class="text-gray-500 dark:text-gray-400">Post not found</p>
                <Link
                    href="/admin/posts"
                    class="mt-2 inline-block text-blue-600 hover:text-blue-500 dark:text-blue-400"
                >
                    Back to Posts
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { usePostsStore } from '@/stores/posts';
import AppLayout from '@/layouts/AppLayout.vue';

interface Props {
    id: number | string;
}

const props = defineProps<Props>();

const postsStore = usePostsStore();
const { currentPost, loading, error } = postsStore;

const fetchPost = async () => {
    const propId =
        typeof props.id !== 'undefined' ? Number(props.id) : undefined;
    const id = propId ?? currentPost?.id;
    if (id) {
        await postsStore.fetchPost(Number(id));
    }
};

onMounted(() => {
    fetchPost();
});
</script>
