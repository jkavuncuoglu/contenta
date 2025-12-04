<template>
    <Head title="Posts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Page header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-semibold text-neutral-900 dark:text-white"
                    >
                        Posts
                    </h1>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Manage your blog posts and articles
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        href="/admin/posts/calendar"
                        class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                    >
                        <Icon
                            name="calendar"
                            icon="material-symbols-light:calendar-month"
                            class="mr-2 h-4 w-4"
                        />
                        Calendar
                    </Link>
                    <Link
                        href="/admin/posts/create"
                        class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                    >
                        <Icon
                            name="Create New Post"
                            icon="material-symbols-light:post-add"
                            class="mr-2 h-5 w-5"
                        />
                        New Post
                    </Link>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-neutral-200 dark:border-neutral-700">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="activeTab = 'all'"
                        :class="[
                            activeTab === 'all'
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300',
                            'border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap',
                        ]"
                    >
                        All Posts
                    </button>
                    <button
                        @click="activeTab = 'archived'"
                        :class="[
                            activeTab === 'archived'
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300',
                            'border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap',
                        ]"
                    >
                        Archived
                        <span
                            v-if="archivedCount > 0"
                            class="ml-2 rounded-full bg-neutral-200 px-2 py-0.5 text-xs text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300"
                        >
                            {{ archivedCount }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- All Posts Tab -->
            <div v-if="activeTab === 'all'" class="space-y-4">
                <!-- View controls and filters -->
                <div
                    class="flex items-center justify-between rounded-lg bg-white p-4 shadow dark:bg-neutral-800"
                >
                    <div class="flex items-center gap-4">
                        <!-- View Toggle -->
                        <div
                            class="flex items-center gap-2 rounded-md border border-neutral-300 p-1 dark:border-neutral-600"
                        >
                            <button
                                @click="viewMode = 'list'"
                                :class="[
                                    viewMode === 'list'
                                        ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-700 dark:text-white'
                                        : 'text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300',
                                    'rounded px-3 py-1.5 text-sm font-medium transition-colors',
                                ]"
                                title="List view"
                            >
                                <Icon
                                    name="Create New Post"
                                    icon="material-symbols-light:list-alt-outline"
                                    class="h-8 w-8"
                                />
                            </button>
                            <button
                                @click="viewMode = 'kanban'"
                                :class="[
                                    viewMode === 'kanban'
                                        ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-700 dark:text-white'
                                        : 'text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300',
                                    'rounded px-3 py-1.5 text-sm font-medium transition-colors',
                                ]"
                                title="Kanban view"
                            >
                                <Icon
                                    name="Create New Post"
                                    icon="material-symbols-light:view-kanban"
                                    class="h-8 w-8"
                                />
                            </button>
                        </div>

                        <!-- Status Filter (for list view) -->
                        <select
                            v-if="viewMode === 'list'"
                            v-model="statusFilter"
                            @change="loadPosts"
                            class="block px-3 py-2 rounded-md border-neutral-300 text-sm shadow-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-300"
                        >
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="published">Published</option>
                        </select>
                    </div>

                    <div class="text-sm text-neutral-500 dark:text-neutral-400">
                        {{ filteredPosts.length }} post{{
                            filteredPosts.length !== 1 ? 's' : ''
                        }}
                    </div>
                </div>

                <!-- List View -->
                <div
                    v-if="viewMode === 'list'"
                    class="overflow-hidden rounded-lg bg-white shadow dark:bg-neutral-800"
                >
                    <div v-if="loading" class="p-8 text-center">
                        <div
                            class="inline-block h-8 w-8 animate-spin rounded-full border-b-2 border-blue-600"
                        ></div>
                    </div>
                    <div
                        v-else-if="filteredPosts.length === 0"
                        class="p-6 text-center"
                    >
                        <Icon
                            name="document-text"
                            class="mx-auto h-12 w-12 text-neutral-400"
                        />
                        <h3
                            class="mt-2 text-sm font-medium text-neutral-900 dark:text-white"
                        >
                            No posts
                        </h3>
                        <p
                            class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                        >
                            Get started by creating your first post.
                        </p>
                    </div>
                    <table
                        v-else
                        class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700"
                    >
                        <thead class="bg-neutral-50 dark:bg-neutral-900">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase dark:text-neutral-400"
                                >
                                    Title
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase dark:text-neutral-400"
                                >
                                    Author
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase dark:text-neutral-400"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase dark:text-neutral-400"
                                >
                                    Date
                                </th>
                                <th class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800"
                        >
                            <tr
                                v-for="post in filteredPosts"
                                :key="post.id"
                                class="hover:bg-neutral-50 dark:hover:bg-neutral-700"
                            >
                                <td class="px-6 py-4">
                                    <p
                                        class="truncate text-sm font-medium text-neutral-900 dark:text-white"
                                    >
                                        {{ post.title }}
                                    </p>
                                    <p
                                        class="truncate text-sm text-neutral-500 dark:text-neutral-400"
                                    >
                                        {{ post.excerpt || 'No excerpt' }}
                                    </p>
                                </td>
                                <td
                                    class="px-6 py-4 text-sm whitespace-nowrap text-neutral-900 dark:text-white"
                                >
                                    {{ post.author?.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getStatusClass(post.status)"
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    >
                                        {{ post.status }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 text-sm whitespace-nowrap text-neutral-500 dark:text-neutral-400"
                                >
                                    {{ formatDate(post.created_at) }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                                >
                                    <Link
                                        :href="`/admin/posts/${post.id}/edit`"
                                        class="mr-4 text-blue-600 hover:text-blue-500"
                                    >
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Kanban View -->
                <div
                    v-else-if="viewMode === 'kanban'"
                    class="grid grid-cols-3 gap-6"
                >
                    <!-- Draft Column -->
                    <div class="rounded-lg bg-neutral-50 p-4 dark:bg-neutral-900">
                        <div class="mb-4 flex items-center justify-between">
                            <h3
                                class="flex items-center font-semibold text-neutral-900 dark:text-white"
                            >
                                <span
                                    class="mr-2 inline-block h-3 w-3 rounded-full bg-yellow-500"
                                ></span>
                                Draft
                            </h3>
                            <span
                                class="text-sm text-neutral-500 dark:text-neutral-400"
                                >{{ draftPosts.length }}</span
                            >
                        </div>
                        <div class="min-h-96 space-y-3">
                            <div
                                v-for="post in draftPosts"
                                :key="post.id"
                                draggable="true"
                                @dragstart="handleDragStart(post, 'draft')"
                                @dragend="handleDragEnd"
                                @drop="handleDrop($event, 'draft')"
                                @dragover.prevent
                                class="cursor-move rounded-lg border-l-4 border-yellow-500 bg-white p-4 shadow transition-shadow hover:shadow-md dark:bg-neutral-800"
                            >
                                <h4
                                    class="mb-1 font-medium text-neutral-900 dark:text-white"
                                >
                                    {{ post.title }}
                                </h4>
                                <p
                                    class="mb-2 line-clamp-2 text-sm text-neutral-500 dark:text-neutral-400"
                                >
                                    {{ post.excerpt }}
                                </p>
                                <div
                                    class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400"
                                >
                                    <span>{{ post.author?.name }}</span>
                                    <Link
                                        :href="`/admin/posts/${post.id}/edit`"
                                        class="text-blue-600 hover:text-blue-500"
                                    >
                                        Edit
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scheduled Column -->
                    <div class="rounded-lg bg-neutral-50 p-4 dark:bg-neutral-900">
                        <div class="mb-4 flex items-center justify-between">
                            <h3
                                class="flex items-center font-semibold text-neutral-900 dark:text-white"
                            >
                                <span
                                    class="mr-2 inline-block h-3 w-3 rounded-full bg-blue-500"
                                ></span>
                                Scheduled
                            </h3>
                            <span
                                class="text-sm text-neutral-500 dark:text-neutral-400"
                                >{{ scheduledPosts.length }}</span
                            >
                        </div>
                        <div class="min-h-96 space-y-3">
                            <div
                                v-for="post in scheduledPosts"
                                :key="post.id"
                                draggable="true"
                                @dragstart="handleDragStart(post, 'scheduled')"
                                @dragend="handleDragEnd"
                                @drop="handleDrop($event, 'scheduled')"
                                @dragover.prevent
                                class="cursor-move rounded-lg border-l-4 border-blue-500 bg-white p-4 shadow transition-shadow hover:shadow-md dark:bg-neutral-800"
                            >
                                <h4
                                    class="mb-1 font-medium text-neutral-900 dark:text-white"
                                >
                                    {{ post.title }}
                                </h4>
                                <p
                                    class="mb-2 line-clamp-2 text-sm text-neutral-500 dark:text-neutral-400"
                                >
                                    {{ post.excerpt }}
                                </p>
                                <div
                                    class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400"
                                >
                                    <span>{{
                                        formatDate(post.published_at)
                                    }}</span>
                                    <Link
                                        :href="`/admin/posts/${post.id}/edit`"
                                        class="text-blue-600 hover:text-blue-500"
                                    >
                                        Edit
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Published Column -->
                    <div class="rounded-lg bg-neutral-50 p-4 dark:bg-neutral-900">
                        <div class="mb-4 flex items-center justify-between">
                            <h3
                                class="flex items-center font-semibold text-neutral-900 dark:text-white"
                            >
                                <span
                                    class="mr-2 inline-block h-3 w-3 rounded-full bg-green-500"
                                ></span>
                                Published
                            </h3>
                            <span
                                class="text-sm text-neutral-500 dark:text-neutral-400"
                                >{{ publishedPosts.length }}</span
                            >
                        </div>
                        <div class="min-h-96 space-y-3">
                            <div
                                v-for="post in publishedPosts"
                                :key="post.id"
                                draggable="true"
                                @dragstart="handleDragStart(post, 'published')"
                                @dragend="handleDragEnd"
                                @drop="handleDrop($event, 'published')"
                                @dragover.prevent
                                class="cursor-move rounded-lg border-l-4 border-green-500 bg-white p-4 shadow transition-shadow hover:shadow-md dark:bg-neutral-800"
                            >
                                <h4
                                    class="mb-1 font-medium text-neutral-900 dark:text-white"
                                >
                                    {{ post.title }}
                                </h4>
                                <p
                                    class="mb-2 line-clamp-2 text-sm text-neutral-500 dark:text-neutral-400"
                                >
                                    {{ post.excerpt }}
                                </p>
                                <div
                                    class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400"
                                >
                                    <span>{{
                                        formatDate(post.published_at)
                                    }}</span>
                                    <Link
                                        :href="`/admin/posts/${post.id}/edit`"
                                        class="text-blue-600 hover:text-blue-500"
                                    >
                                        Edit
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Archived Tab -->
            <div
                v-else-if="activeTab === 'archived'"
                class="overflow-hidden rounded-lg bg-white shadow dark:bg-neutral-800"
            >
                <div v-if="loadingArchived" class="p-8 text-center">
                    <div
                        class="inline-block h-8 w-8 animate-spin rounded-full border-b-2 border-blue-600"
                    ></div>
                </div>
                <div
                    v-else-if="archivedPosts.length === 0"
                    class="p-6 text-center"
                >
                    <Icon
                        name="archive-box"
                        class="mx-auto h-12 w-12 text-neutral-400"
                    />
                    <h3
                        class="mt-2 text-sm font-medium text-neutral-900 dark:text-white"
                    >
                        No archived posts
                    </h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Deleted posts will appear here.
                    </p>
                </div>
                <table
                    v-else
                    class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700"
                >
                    <thead class="bg-neutral-50 dark:bg-neutral-900">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase dark:text-neutral-400"
                            >
                                Title
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase dark:text-neutral-400"
                            >
                                Author
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase dark:text-neutral-400"
                            >
                                Deleted
                            </th>
                            <th class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800"
                    >
                        <tr
                            v-for="post in archivedPosts"
                            :key="post.id"
                            class="hover:bg-neutral-50 dark:hover:bg-neutral-700"
                        >
                            <td class="px-6 py-4">
                                <p
                                    class="text-sm font-medium text-neutral-900 dark:text-white"
                                >
                                    {{ post.title }}
                                </p>
                                <p
                                    class="text-sm text-neutral-500 dark:text-neutral-400"
                                >
                                    {{ post.excerpt || 'No excerpt' }}
                                </p>
                            </td>
                            <td
                                class="px-6 py-4 text-sm whitespace-nowrap text-neutral-900 dark:text-white"
                            >
                                {{ post.author?.name }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm whitespace-nowrap text-neutral-500 dark:text-neutral-400"
                            >
                                {{ formatDate(post.deleted_at) }}
                            </td>
                            <td
                                class="space-x-4 px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                            >
                                <Link
                                    :href="`/admin/posts/${post.id}/edit`"
                                    class="text-blue-600 hover:text-blue-500"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="restorePost(post.id)"
                                    class="text-green-600 hover:text-green-500"
                                >
                                    Restore
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Icon } from '@iconify/vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const breadcrumbs = [{ label: 'Posts', href: '/admin/posts' }];

interface Post {
    id: number;
    title: string;
    excerpt?: string;
    status: string;
    created_at: string;
    published_at?: string;
    deleted_at?: string;
    author?: {
        name: string;
    };
}

const activeTab = ref<'all' | 'archived'>('all');
const viewMode = ref<'list' | 'kanban'>('list');
const statusFilter = ref('');
const posts = ref<Post[]>([]);
const archivedPosts = ref<Post[]>([]);
const loading = ref(false);
const loadingArchived = ref(false);
const draggedPost = ref<Post | null>(null);
const draggedFromColumn = ref<string | null>(null);

const filteredPosts = computed(() => {
    if (!statusFilter.value) return posts.value;
    return posts.value.filter((post) => post.status === statusFilter.value);
});

const draftPosts = computed(() =>
    posts.value.filter((p) => p.status === 'draft'),
);
const scheduledPosts = computed(() =>
    posts.value.filter((p) => p.status === 'scheduled'),
);
const publishedPosts = computed(() =>
    posts.value.filter((p) => p.status === 'published'),
);
const archivedCount = computed(() => archivedPosts.value.length);

async function loadPosts() {
    loading.value = true;
    try {
        const url = statusFilter.value
            ? `/admin/api/posts?status=${statusFilter.value}&per_page=100`
            : '/admin/api/posts?per_page=100';
        const response = await fetch(url);
        const data = await response.json();
        posts.value = data.data || [];
    } catch (error) {
        console.error('Failed to load posts:', error);
    } finally {
        loading.value = false;
    }
}

async function loadArchivedPosts() {
    loadingArchived.value = true;
    try {
        const response = await fetch('/admin/api/posts/archived?per_page=100');
        const data = await response.json();
        archivedPosts.value = data.data || [];
    } catch (error) {
        console.error('Failed to load archived posts:', error);
    } finally {
        loadingArchived.value = false;
    }
}

async function restorePost(postId: number) {
    if (!confirm('Restore this post to draft status?')) return;

    try {
        const response = await fetch(`/admin/api/posts/${postId}/restore`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || '',
            },
        });

        if (response.ok) {
            archivedPosts.value = archivedPosts.value.filter(
                (p) => p.id !== postId,
            );
            await loadPosts();
            router.reload({ only: ['posts'] });
        }
    } catch (error) {
        console.error('Failed to restore post:', error);
    }
}

async function changePostStatus(postId: number, newStatus: string) {
    try {
        const response = await fetch(`/admin/api/posts/${postId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || '',
            },
            body: JSON.stringify({ status: newStatus }),
        });

        if (response.ok) {
            await loadPosts();
        }
    } catch (error) {
        console.error('Failed to change post status:', error);
    }
}

function handleDragStart(post: Post, column: string) {
    draggedPost.value = post;
    draggedFromColumn.value = column;
}

function handleDragEnd() {
    draggedPost.value = null;
    draggedFromColumn.value = null;
}

async function handleDrop(event: DragEvent, targetColumn: string) {
    event.preventDefault();

    if (!draggedPost.value || draggedFromColumn.value === targetColumn) {
        return;
    }

    await changePostStatus(draggedPost.value.id, targetColumn);
}

function getStatusClass(status: string) {
    const classes: Record<string, string> = {
        published:
            'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
        draft: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
        scheduled:
            'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
    };
    return (
        classes[status] ||
        'bg-neutral-100 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-200'
    );
}

function formatDate(dateString: string | undefined) {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

onMounted(() => {
    loadPosts();
    loadArchivedPosts();
});
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
