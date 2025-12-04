<template>
    <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
        <h3 class="mb-4 text-lg font-medium text-neutral-900 dark:text-white">SEO Score</h3>

        <!-- Overall Score Circle -->
        <div class="mb-6 flex items-center justify-center">
            <div class="relative h-40 w-40">
                <svg class="h-full w-full -rotate-90 transform">
                    <!-- Background circle -->
                    <circle
                        cx="80"
                        cy="80"
                        r="70"
                        stroke="currentColor"
                        stroke-width="12"
                        fill="none"
                        class="text-neutral-200 dark:text-neutral-700"
                    />
                    <!-- Progress circle -->
                    <circle
                        cx="80"
                        cy="80"
                        r="70"
                        :stroke="scoreColor"
                        stroke-width="12"
                        fill="none"
                        stroke-linecap="round"
                        :stroke-dasharray="circumference"
                        :stroke-dashoffset="dashOffset"
                        class="transition-all duration-500"
                    />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-bold" :class="scoreColorClass">
                        {{ score.overall }}
                    </span>
                    <span class="text-sm text-neutral-500 dark:text-neutral-400">/ 100</span>
                </div>
            </div>
        </div>

        <!-- Status Badge -->
        <div class="mb-6 text-center">
            <span
                :class="[
                    'inline-flex items-center rounded-full px-3 py-1 text-sm font-medium',
                    statusClasses,
                ]"
            >
                {{ statusText }}
            </span>
        </div>

        <!-- Score Breakdown -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-neutral-600 dark:text-neutral-300">Keyword Optimization</span>
                <div class="flex items-center gap-2">
                    <div class="h-2 w-24 overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
                        <div
                            class="h-full transition-all duration-500"
                            :style="{ width: `${score.keyword}%` }"
                            :class="getBarColor(score.keyword)"
                        ></div>
                    </div>
                    <span class="w-8 text-right text-sm font-medium" :class="getTextColor(score.keyword)">
                        {{ score.keyword }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm text-neutral-600 dark:text-neutral-300">Content Quality</span>
                <div class="flex items-center gap-2">
                    <div class="h-2 w-24 overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
                        <div
                            class="h-full transition-all duration-500"
                            :style="{ width: `${score.content}%` }"
                            :class="getBarColor(score.content)"
                        ></div>
                    </div>
                    <span class="w-8 text-right text-sm font-medium" :class="getTextColor(score.content)">
                        {{ score.content }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm text-neutral-600 dark:text-neutral-300">Readability</span>
                <div class="flex items-center gap-2">
                    <div class="h-2 w-24 overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
                        <div
                            class="h-full transition-all duration-500"
                            :style="{ width: `${score.readability}%` }"
                            :class="getBarColor(score.readability)"
                        ></div>
                    </div>
                    <span class="w-8 text-right text-sm font-medium" :class="getTextColor(score.readability)">
                        {{ score.readability }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm text-neutral-600 dark:text-neutral-300">Technical SEO</span>
                <div class="flex items-center gap-2">
                    <div class="h-2 w-24 overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
                        <div
                            class="h-full transition-all duration-500"
                            :style="{ width: `${score.technical}%` }"
                            :class="getBarColor(score.technical)"
                        ></div>
                    </div>
                    <span class="w-8 text-right text-sm font-medium" :class="getTextColor(score.technical)">
                        {{ score.technical }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Priority Issues -->
        <div v-if="highPriorityCount > 0" class="mt-6 rounded-md bg-red-50 p-4 dark:bg-red-900/20">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                        {{ highPriorityCount }} High Priority {{ highPriorityCount === 1 ? 'Issue' : 'Issues' }}
                    </h3>
                    <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                        Address these critical SEO issues to improve your search ranking.
                    </p>
                </div>
            </div>
        </div>

        <!-- Suggestions -->
        <div v-else-if="mediumPriorityCount > 0" class="mt-6 rounded-md bg-yellow-50 p-4 dark:bg-yellow-900/20">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        {{ mediumPriorityCount }} {{ mediumPriorityCount === 1 ? 'Suggestion' : 'Suggestions' }}
                    </h3>
                    <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                        Make these improvements to optimize your content further.
                    </p>
                </div>
            </div>
        </div>

        <!-- All Good -->
        <div v-else-if="score.overall >= 85" class="mt-6 rounded-md bg-green-50 p-4 dark:bg-green-900/20">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Excellent SEO!</h3>
                    <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                        Your content is well-optimized for search engines.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { SEOScore } from '@/composables/seo/useSEOAnalysis';

interface Props {
    score: SEOScore;
    status: 'excellent' | 'good' | 'needs-improvement' | 'poor';
    highPriorityCount: number;
    mediumPriorityCount: number;
}

const props = defineProps<Props>();

const circumference = 2 * Math.PI * 70; // Circle circumference

const dashOffset = computed(() => {
    const progress = props.score.overall / 100;
    return circumference * (1 - progress);
});

const scoreColor = computed(() => {
    if (props.score.overall >= 85) return '#10b981'; // green
    if (props.score.overall >= 70) return '#3b82f6'; // blue
    if (props.score.overall >= 50) return '#f59e0b'; // yellow
    return '#ef4444'; // red
});

const scoreColorClass = computed(() => {
    if (props.score.overall >= 85) return 'text-green-600 dark:text-green-400';
    if (props.score.overall >= 70) return 'text-blue-600 dark:text-blue-400';
    if (props.score.overall >= 50) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
});

const statusText = computed(() => {
    switch (props.status) {
        case 'excellent':
            return 'Excellent';
        case 'good':
            return 'Good';
        case 'needs-improvement':
            return 'Needs Improvement';
        case 'poor':
            return 'Poor';
        default:
            return 'Unknown';
    }
});

const statusClasses = computed(() => {
    switch (props.status) {
        case 'excellent':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
        case 'good':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
        case 'needs-improvement':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300';
        case 'poor':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300';
        default:
            return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-300';
    }
});

const getBarColor = (score: number) => {
    if (score >= 85) return 'bg-green-500';
    if (score >= 70) return 'bg-blue-500';
    if (score >= 50) return 'bg-yellow-500';
    return 'bg-red-500';
};

const getTextColor = (score: number) => {
    if (score >= 85) return 'text-green-600 dark:text-green-400';
    if (score >= 70) return 'text-blue-600 dark:text-blue-400';
    if (score >= 50) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
};
</script>
