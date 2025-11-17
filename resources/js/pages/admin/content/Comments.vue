<template>
    <Head title="Comments" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                    >
                        Comments
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Manage and moderate comments on your posts
                    </p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <h3
                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                    >
                        Total
                    </h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ statistics.total }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <h3
                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                    >
                        Pending
                    </h3>
                    <p class="text-2xl font-bold text-yellow-600">
                        {{ statistics.pending }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <h3
                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                    >
                        Approved
                    </h3>
                    <p class="text-2xl font-bold text-green-600">
                        {{ statistics.approved }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <h3
                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                    >
                        Spam
                    </h3>
                    <p class="text-2xl font-bold text-red-600">
                        {{ statistics.spam }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <h3
                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                    >
                        Trash
                    </h3>
                    <p class="text-2xl font-bold text-gray-600">
                        {{ statistics.trash }}
                    </p>
                </div>
            </div>

            <!-- Flash Messages -->
            <div
                v-if="flashMessage?.success"
                class="rounded-md border border-green-200 bg-green-50 p-4"
            >
                <div class="flex">
                    <Icon
                        icon="material-symbols-light:check-circle"
                        class="h-5 w-5 text-green-400"
                    />
                    <div class="ml-3">
                        <p class="text-sm text-green-800">
                            {{ flashMessage.success }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="flashMessage?.error"
                class="rounded-md border border-red-200 bg-red-50 p-4"
            >
                <div class="flex">
                    <Icon
                        icon="material-symbols-light:error"
                        class="h-5 w-5 text-red-400"
                    />
                    <div class="ml-3">
                        <p class="text-sm text-red-800">
                            {{ flashMessage.error }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <div class="flex flex-col gap-4 sm:flex-row">
                    <div class="flex-1">
                        <input
                            v-model="searchTerm"
                            type="text"
                            placeholder="Search comments..."
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            @input="debouncedSearch"
                        />
                    </div>
                    <div>
                        <select
                            v-model="selectedStatus"
                            class="rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            @change="filterComments"
                        >
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="spam">Spam</option>
                            <option value="trash">Trash</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div
                v-if="selectedComments.length > 0"
                class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20"
            >
                <div class="flex items-center justify-between">
                    <span class="text-sm text-blue-700 dark:text-blue-300">
                        {{ selectedComments.length }} comment{{
                            selectedComments.length > 1 ? 's' : ''
                        }}
                        selected
                    </span>
                    <div class="flex gap-2">
                        <button
                            @click="bulkUpdateStatus('approved')"
                            class="rounded bg-green-600 px-3 py-1 text-sm text-white hover:bg-green-700"
                        >
                            Approve
                        </button>
                        <button
                            @click="bulkUpdateStatus('spam')"
                            class="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700"
                        >
                            Mark as Spam
                        </button>
                        <button
                            @click="bulkUpdateStatus('trash')"
                            class="rounded bg-gray-600 px-3 py-1 text-sm text-white hover:bg-gray-700"
                        >
                            Trash
                        </button>
                    </div>
                </div>
            </div>

            <!-- Comments List -->
            <div class="rounded-lg bg-white shadow dark:bg-gray-800">
                <div v-if="!comments.length" class="p-12 text-center">
                    <Icon
                        icon="material-symbols-light:chat-bubble-outline"
                        class="mx-auto h-12 w-12 text-gray-400"
                    />
                    <h3
                        class="mt-2 text-sm font-medium text-gray-900 dark:text-white"
                    >
                        No comments
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        No comments match your current filters.
                    </p>
                </div>

                <div
                    v-else
                    class="divide-y divide-gray-200 dark:divide-gray-700"
                >
                    <div
                        v-for="comment in comments"
                        :key="comment.id"
                        class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        <div class="flex items-start space-x-4">
                            <input
                                v-model="selectedComments"
                                :value="comment.id"
                                type="checkbox"
                                class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />

                            <div class="min-w-0 flex-1">
                                <!-- Comment Header -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <h4
                                            class="text-sm font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ comment.author_name }}
                                        </h4>
                                        <span
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            {{ comment.author_email }}
                                        </span>
                                        <span
                                            :class="
                                                getStatusClass(comment.status)
                                            "
                                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                        >
                                            {{ comment.status }}
                                        </span>
                                        <span
                                            v-if="comment.is_reply"
                                            class="inline-flex items-center rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-300"
                                        >
                                            Reply
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            {{ comment.created_at }}
                                        </span>
                                        <div class="relative">
                                            <button
                                                @click="
                                                    toggleCommentMenu(
                                                        comment.id,
                                                    )
                                                "
                                                class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                            >
                                                <Icon
                                                    icon="material-symbols-light:more-vert"
                                                    class="h-4 w-4"
                                                />
                                            </button>
                                            <div
                                                v-if="openMenuId === comment.id"
                                                class="absolute right-0 z-10 mt-2 w-48 rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                                            >
                                                <div class="py-1">
                                                    <button
                                                        v-if="
                                                            comment.status !==
                                                            'approved'
                                                        "
                                                        @click="
                                                            updateCommentStatus(
                                                                comment.id,
                                                                'approved',
                                                            )
                                                        "
                                                        class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                                    >
                                                        Approve
                                                    </button>
                                                    <button
                                                        v-if="
                                                            comment.status !==
                                                            'pending'
                                                        "
                                                        @click="
                                                            updateCommentStatus(
                                                                comment.id,
                                                                'pending',
                                                            )
                                                        "
                                                        class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                                    >
                                                        Mark as Pending
                                                    </button>
                                                    <button
                                                        v-if="
                                                            comment.status !==
                                                            'spam'
                                                        "
                                                        @click="
                                                            updateCommentStatus(
                                                                comment.id,
                                                                'spam',
                                                            )
                                                        "
                                                        class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                                    >
                                                        Mark as Spam
                                                    </button>
                                                    <button
                                                        @click="
                                                            deleteComment(
                                                                comment.id,
                                                            )
                                                        "
                                                        class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Post Info -->
                                <div class="mt-1">
                                    <span
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        On:
                                        <a
                                            :href="`/posts/${comment.post_slug}`"
                                            class="text-blue-600 hover:text-blue-800"
                                        >
                                            {{ comment.post_title }}
                                        </a>
                                    </span>
                                </div>

                                <!-- Comment Content -->
                                <div class="mt-2">
                                    <p
                                        class="text-sm text-gray-900 dark:text-white"
                                    >
                                        {{ comment.content }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="pagination.last_page > 1"
                class="rounded-lg bg-white px-4 py-3 shadow dark:bg-gray-800"
            >
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Showing {{ pagination.from }} to {{ pagination.to }} of
                        {{ pagination.total }} comments
                    </div>
                    <div class="flex space-x-2">
                        <button
                            v-for="page in visiblePages"
                            :key="page"
                            @click="goToPage(page)"
                            :class="{
                                'bg-blue-600 text-white':
                                    page === pagination.current_page,
                                'bg-gray-200 text-gray-700 hover:bg-gray-300':
                                    page !== pagination.current_page,
                            }"
                            class="rounded px-3 py-1 text-sm"
                        >
                            {{ page }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Icon } from '@iconify/vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, withDefaults } from 'vue';

interface Comment {
    id: number;
    post_id: number;
    post_title: string;
    post_slug: string;
    parent_id: number | null;
    author_name: string;
    author_email: string;
    content: string;
    content_excerpt: string;
    status: 'pending' | 'approved' | 'spam' | 'trash';
    created_at: string;
    updated_at: string;
    is_reply: boolean;
}

interface Statistics {
    total: number;
    pending: number;
    approved: number;
    spam: number;
    trash: number;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Props {
    comments: Comment[];
    pagination: Pagination;
    filters: Record<string, any>;
    statistics: Statistics;
}

const props = withDefaults(defineProps<Props>(), {
    comments: () => [],
    filters: () => ({}),
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Comments', href: '/admin/comments' },
];

const page = usePage();
const flashMessage = computed(() => page.props.flash);

// State
const searchTerm = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedComments = ref<number[]>([]);
const openMenuId = ref<number | null>(null);

// Computed
const visiblePages = computed(() => {
    const current = props.pagination.current_page;
    const last = props.pagination.last_page;
    const pages = [];

    for (
        let i = Math.max(1, current - 2);
        i <= Math.min(last, current + 2);
        i++
    ) {
        pages.push(i);
    }

    return pages;
});

// Methods
const debouncedSearch = debounce(() => {
    filterComments();
}, 500);

const filterComments = () => {
    const filters: Record<string, any> = {};

    if (searchTerm.value) {
        filters.search = searchTerm.value;
    }

    if (selectedStatus.value) {
        filters.status = selectedStatus.value;
    }

    router.get('/admin/comments', filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const updateCommentStatus = async (id: number, status: string) => {
    router.patch(
        `/admin/comments/${id}/status`,
        { status },
        {
            preserveState: false,
            preserveScroll: false,
        },
    );

    openMenuId.value = null;
};

const bulkUpdateStatus = async (status: string) => {
    if (selectedComments.value.length === 0) return;

    router.patch(
        '/admin/comments/bulk-status',
        {
            ids: selectedComments.value,
            status,
        },
        {
            preserveState: false,
            preserveScroll: false,
        },
    );

    selectedComments.value = [];
};

const deleteComment = async (id: number) => {
    if (
        !confirm(
            'Are you sure you want to delete this comment? This action cannot be undone.',
        )
    ) {
        return;
    }

    router.delete(`/admin/comments/${id}`, {
        preserveState: false,
        preserveScroll: false,
    });

    openMenuId.value = null;
};

const toggleCommentMenu = (id: number) => {
    openMenuId.value = openMenuId.value === id ? null : id;
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300';
        case 'spam':
            return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300';
        case 'trash':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-300';
    }
};

const goToPage = (page: number) => {
    router.get(
        '/admin/comments',
        {
            ...props.filters,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

// Utility function
function debounce(func: (...args: any[]) => void, wait: number) {
    let timeout: NodeJS.Timeout;
    return function executedFunction(...args: any[]) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Close dropdown when clicking outside
watch(
    () => openMenuId.value,
    () => {
        if (openMenuId.value !== null) {
            document.addEventListener(
                'click',
                () => {
                    openMenuId.value = null;
                },
                { once: true },
            );
        }
    },
);
</script>
