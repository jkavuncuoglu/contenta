<template>
    <Head title="Content Calendar" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Page header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-semibold text-neutral-900 dark:text-white"
                    >
                        Content Calendar
                    </h1>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        View and manage your content schedule
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <Link
                        href="/admin/posts"
                        class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                    >
                        <Icon
                            name="List View"
                            icon="material-symbols-light:format-list-bulleted"
                            class="mr-2 h-5 w-5"
                        />
                        List View
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

            <!-- Calendar controls -->
            <div class="rounded-lg bg-white p-4 shadow dark:bg-neutral-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <button
                            @click="previousMonth"
                            class="rounded p-2 hover:bg-neutral-100 dark:hover:bg-neutral-700"
                            title="Previous month"
                        >
                            <Icon name="chevron-left" class="h-5 w-5" />
                        </button>
                        <button
                            @click="nextMonth"
                            class="rounded p-2 hover:bg-neutral-100 dark:hover:bg-neutral-700"
                            title="Next month"
                        >
                            <Icon name="chevron-right" class="h-5 w-5" />
                        </button>
                        <button
                            @click="goToToday"
                            class="rounded border border-neutral-300 px-3 py-1 text-sm hover:bg-neutral-100 dark:border-neutral-600 dark:hover:bg-neutral-700"
                        >
                            Today
                        </button>
                    </div>
                    <h2
                        class="text-lg font-semibold text-neutral-900 dark:text-white"
                    >
                        {{ currentMonthYear }}
                    </h2>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-2 text-sm">
                            <span
                                class="inline-block h-3 w-3 rounded-full bg-green-500"
                            ></span>
                            <span class="text-neutral-600 dark:text-neutral-400"
                                >Published</span
                            >
                            <span
                                class="ml-4 inline-block h-3 w-3 rounded-full bg-yellow-500"
                            ></span>
                            <span class="text-neutral-600 dark:text-neutral-400"
                                >Scheduled</span
                            >
                            <span
                                class="ml-4 inline-block h-3 w-3 rounded-full bg-neutral-400"
                            ></span>
                            <span class="text-neutral-600 dark:text-neutral-400"
                                >Draft</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar grid -->
            <div
                class="overflow-hidden rounded-lg bg-white shadow dark:bg-neutral-800"
            >
                <div
                    class="grid grid-cols-7 border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900"
                >
                    <div
                        v-for="day in weekDays"
                        :key="day"
                        class="px-4 py-2 text-center text-xs font-semibold tracking-wide text-neutral-700 uppercase dark:text-neutral-300"
                    >
                        {{ day }}
                    </div>
                </div>
                <div class="grid auto-rows-fr grid-cols-7">
                    <div
                        v-for="(day, index) in calendarDays"
                        :key="index"
                        :class="[
                            'min-h-32 border-r border-b border-neutral-200 p-2 dark:border-neutral-700',
                            !day.isCurrentMonth &&
                                'bg-neutral-50 dark:bg-neutral-900/50',
                            day.isToday && 'bg-blue-50 dark:bg-blue-900/20',
                        ]"
                    >
                        <div class="mb-1 flex items-center justify-between">
                            <span
                                :class="[
                                    'text-sm font-medium',
                                    day.isCurrentMonth
                                        ? 'text-neutral-900 dark:text-white'
                                        : 'text-neutral-400 dark:text-neutral-600',
                                    day.isToday &&
                                        'flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white',
                                ]"
                            >
                                {{ day.date.getDate() }}
                            </span>
                        </div>
                        <div class="space-y-1">
                            <div
                                v-for="post in getPostsForDay(day.date)"
                                :key="post.id"
                                @click="openPostDetails(post)"
                                :class="[
                                    'cursor-pointer rounded p-1 text-xs transition-shadow hover:shadow-md',
                                    getPostClass(post.status),
                                ]"
                                :title="post.title"
                            >
                                <div class="truncate font-medium">
                                    {{ post.title }}
                                </div>
                                <div
                                    class="text-[10px] text-neutral-600 dark:text-neutral-400"
                                >
                                    {{ formatTime(post.published_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scheduled Posts List -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                <h3
                    class="mb-4 text-lg font-semibold text-neutral-900 dark:text-white"
                >
                    Upcoming Scheduled Posts
                </h3>
                <div v-if="loading" class="py-8 text-center">
                    <div
                        class="inline-block h-8 w-8 animate-spin rounded-full border-b-2 border-blue-600"
                    ></div>
                </div>
                <div
                    v-else-if="scheduledPosts.length === 0"
                    class="py-8 text-center"
                >
                    <Icon
                        name="calendar"
                        class="mx-auto h-12 w-12 text-neutral-400"
                    />
                    <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                        No scheduled posts
                    </p>
                </div>
                <div v-else class="space-y-3">
                    <div
                        v-for="post in scheduledPosts"
                        :key="post.id"
                        class="flex items-center justify-between rounded-lg border border-neutral-200 p-4 hover:bg-neutral-50 dark:border-neutral-700 dark:hover:bg-neutral-700"
                    >
                        <div class="flex-1">
                            <h4
                                class="font-medium text-neutral-900 dark:text-white"
                            >
                                {{ post.title }}
                            </h4>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                Scheduled for
                                {{ formatDateTime(post.published_at) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link
                                :href="`/admin/posts/${post.id}/edit`"
                                class="rounded p-2 hover:bg-neutral-100 dark:hover:bg-neutral-600"
                                title="Edit post"
                            >
                                <Icon name="pencil" class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { Icon } from '@iconify/vue';

const breadcrumbs = [
    { label: 'Admin', href: '/admin' },
    { label: 'Content', href: '/admin/posts' },
    { label: 'Calendar', href: '/admin/posts/calendar' },
];

interface Post {
    id: number;
    title: string;
    slug: string;
    status: 'published' | 'scheduled' | 'draft';
    published_at: string;
    author: {
        id: number;
        name: string;
    };
    categories: Array<{
        id: number;
        name: string;
    }>;
}

const currentDate = ref(new Date());
const posts = ref<Post[]>([]);
const scheduledPosts = ref<Post[]>([]);
const loading = ref(false);

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const currentMonthYear = computed(() => {
    return currentDate.value.toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric',
    });
});

const calendarDays = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();

    const firstDayOfMonth = new Date(year, month, 1);
    const lastDayOfMonth = new Date(year, month + 1, 0);
    const firstDayOfWeek = firstDayOfMonth.getDay();
    const daysInMonth = lastDayOfMonth.getDate();

    const days = [];
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Previous month days
    const prevMonthLastDay = new Date(year, month, 0).getDate();
    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        const date = new Date(year, month - 1, prevMonthLastDay - i);
        days.push({
            date,
            isCurrentMonth: false,
            isToday: false,
        });
    }

    // Current month days
    for (let i = 1; i <= daysInMonth; i++) {
        const date = new Date(year, month, i);
        days.push({
            date,
            isCurrentMonth: true,
            isToday: date.getTime() === today.getTime(),
        });
    }

    // Next month days
    const remainingDays = 42 - days.length;
    for (let i = 1; i <= remainingDays; i++) {
        const date = new Date(year, month + 1, i);
        days.push({
            date,
            isCurrentMonth: false,
            isToday: false,
        });
    }

    return days;
});

function getPostsForDay(date: Date): Post[] {
    const dateStr = date.toISOString().split('T')[0];
    return posts.value.filter((post) => {
        if (!post.published_at) return false;
        const postDate = new Date(post.published_at)
            .toISOString()
            .split('T')[0];
        return postDate === dateStr;
    });
}

function getPostClass(status: string): string {
    switch (status) {
        case 'published':
            return 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border-l-2 border-green-500';
        case 'scheduled':
            return 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 border-l-2 border-yellow-500';
        default:
            return 'bg-neutral-100 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-200 border-l-2 border-neutral-500';
    }
}

function formatTime(dateTime: string | null): string {
    if (!dateTime) return '';
    const date = new Date(dateTime);

    // Get formatted time
    const formattedTime = date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });

    // Get timezone abbreviation
    const timeZoneString = date.toLocaleTimeString('en-US', {
        timeZoneName: 'short',
    });
    const tzAbbr = timeZoneString.split(' ').pop() || '';

    return `${formattedTime} ${tzAbbr}`;
}

function formatDateTime(dateTime: string | null): string {
    if (!dateTime) return '';
    const date = new Date(dateTime);

    // Get formatted date and time
    const formattedDate = date.toLocaleDateString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });

    // Get timezone abbreviation
    const timeZoneString = date.toLocaleTimeString('en-US', {
        timeZoneName: 'short',
    });
    const tzAbbr = timeZoneString.split(' ').pop() || '';

    return `${formattedDate} ${tzAbbr}`;
}

function previousMonth() {
    currentDate.value = new Date(
        currentDate.value.getFullYear(),
        currentDate.value.getMonth() - 1,
        1,
    );
    fetchCalendarPosts();
}

function nextMonth() {
    currentDate.value = new Date(
        currentDate.value.getFullYear(),
        currentDate.value.getMonth() + 1,
        1,
    );
    fetchCalendarPosts();
}

function goToToday() {
    currentDate.value = new Date();
    fetchCalendarPosts();
}

function openPostDetails(post: Post) {
    router.visit(`/admin/posts/${post.id}/edit`);
}

async function fetchCalendarPosts() {
    loading.value = true;

    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const startDate = new Date(year, month, 1).toISOString().split('T')[0];
    const endDate = new Date(year, month + 1, 0).toISOString().split('T')[0];

    try {
        const response = await fetch(
            `/admin/api/posts/calendar?start_date=${startDate}&end_date=${endDate}`,
        );
        const data = await response.json();
        posts.value = data.data || [];
    } catch (error) {
        console.error('Failed to fetch calendar posts:', error);
    } finally {
        loading.value = false;
    }
}

async function fetchScheduledPosts() {
    try {
        const response = await fetch('/admin/api/posts/scheduled?per_page=10');
        const data = await response.json();
        scheduledPosts.value = data.data || [];
    } catch (error) {
        console.error('Failed to fetch scheduled posts:', error);
    }
}

onMounted(() => {
    fetchCalendarPosts();
    fetchScheduledPosts();
});
</script>
