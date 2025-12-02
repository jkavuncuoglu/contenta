<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { Head } from '@inertiajs/vue3';

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

const props = defineProps<Props>();
const _props = props;

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
        <Head :title="`${siteTitle} - Blog`" />

        <!-- Header -->
        <header class="bg-white shadow dark:bg-neutral-800">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1
                        class="text-3xl font-bold text-neutral-900 dark:text-white"
                    >
                        {{ siteTitle }}
                    </h1>
                    <p
                        v-if="siteTagline"
                        class="mt-2 text-lg text-neutral-600 dark:text-neutral-400"
                    >
                        {{ siteTagline }}
                    </p>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Blog Posts -->
                <div class="lg:col-span-2">
                    <div
                        v-if="posts.data.length === 0"
                        class="py-12 text-center"
                    >
                        <Icon
                            icon="material-symbols-light:article-outline"
                            class="mx-auto h-12 w-12 text-neutral-400"
                        />
                        <h3
                            class="mt-4 text-lg font-medium text-neutral-900 dark:text-white"
                        >
                            No posts yet
                        </h3>
                        <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                            Check back later for new content!
                        </p>
                    </div>

                    <div v-else class="space-y-8">
                        <article
                            v-for="post in posts.data"
                            :key="post.id"
                            class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-neutral-800"
                        >
                            <div class="p-6">
                                <div
                                    class="mb-4 flex items-center text-sm text-neutral-500 dark:text-neutral-400"
                                >
                                    <time :datetime="post.published_at">
                                        {{ formatDate(post.published_at) }}
                                    </time>
                                    <span class="mx-2">•</span>
                                    <span>{{ post.author.name }}</span>
                                    <span v-if="post.post_type" class="mx-2"
                                        >•</span
                                    >
                                    <span
                                        v-if="post.post_type"
                                        class="capitalize"
                                        >{{ post.post_type.name }}</span
                                    >
                                </div>

                                <h2
                                    class="mb-3 text-xl font-bold text-neutral-900 dark:text-white"
                                >
                                    <a
                                        :href="`/blog/${post.slug}`"
                                        class="hover:text-blue-600 dark:hover:text-blue-400"
                                    >
                                        {{ post.title }}
                                    </a>
                                </h2>

                                <p
                                    v-if="post.excerpt"
                                    class="mb-4 text-neutral-600 dark:text-neutral-300"
                                >
                                    {{ post.excerpt }}
                                </p>

                                <div
                                    v-else-if="post.content_html"
                                    class="mb-4 text-neutral-600 dark:text-neutral-300"
                                >
                                    <div
                                        v-html="
                                            post.content_html.substring(
                                                0,
                                                300,
                                            ) + '...'
                                        "
                                        class="prose prose-sm max-w-none"
                                    ></div>
                                </div>

                                <a
                                    :href="`/blog/${post.slug}`"
                                    class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                >
                                    Read more
                                    <Icon
                                        icon="material-symbols-light:arrow-forward"
                                        class="ml-1 h-4 w-4"
                                    />
                                </a>
                            </div>
                        </article>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="posts.links && posts.links.length > 3"
                        class="mt-8"
                    >
                        <nav class="flex justify-center">
                            <div class="flex space-x-1">
                                <a
                                    v-for="link in posts.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'rounded-md px-3 py-2 text-sm',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : link.url
                                              ? 'bg-white text-neutral-700 hover:bg-neutral-50 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700'
                                              : 'cursor-not-allowed bg-neutral-100 text-neutral-400 dark:bg-neutral-800',
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
                    <div
                        class="rounded-lg bg-white p-6 shadow-sm dark:bg-neutral-800"
                    >
                        <h3
                            class="mb-4 text-lg font-semibold text-neutral-900 dark:text-white"
                        >
                            About
                        </h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Welcome to our blog! Here you'll find the latest
                            posts and updates.
                        </p>
                    </div>

                    <!-- Recent Posts Widget -->
                    <div
                        v-if="posts.data.length > 0"
                        class="mt-6 rounded-lg bg-white p-6 shadow-sm dark:bg-neutral-800"
                    >
                        <h3
                            class="mb-4 text-lg font-semibold text-neutral-900 dark:text-white"
                        >
                            Recent Posts
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="post in posts.data.slice(0, 5)"
                                :key="post.id"
                                class="border-b border-neutral-200 pb-3 last:border-b-0 last:pb-0 dark:border-neutral-700"
                            >
                                <a
                                    :href="`/blog/${post.slug}`"
                                    class="block text-sm font-medium text-neutral-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
                                >
                                    {{ post.title }}
                                </a>
                                <p
                                    class="mt-1 text-xs text-neutral-500 dark:text-neutral-400"
                                >
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
