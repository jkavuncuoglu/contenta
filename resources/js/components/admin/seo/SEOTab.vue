<template>
    <div class="space-y-6">
        <!-- Top Row: Target Keyword (Full Width) -->
        <!-- Target Keyword Section -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
            <h3 class="mb-4 text-lg font-medium text-neutral-900 dark:text-white">Target Keyword</h3>

            <div class="space-y-4">
                <div>
                    <label for="target-keyword" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        Primary Keyword
                    </label>
                    <input
                        id="target-keyword"
                        v-model="targetKeyword"
                        type="text"
                        placeholder="e.g., vue seo optimization"
                        class=" mt-1 block w-full py-2 px-3 rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                    />
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Enter the main keyword you want to rank for with this content.
                    </p>
                </div>

                <div v-if="targetKeyword.trim()" class="space-y-3">
                    <!-- Keyword Density -->
                    <div class="rounded-md bg-neutral-50 p-4 dark:bg-neutral-900/30">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                Keyword Density
                            </span>
                            <span
                                :class="[
                                    'text-sm font-semibold',
                                    seoAnalysis.keywordAnalysis.densityStatus.value === 'optimal'
                                        ? 'text-green-600 dark:text-green-400'
                                        : seoAnalysis.keywordAnalysis.densityStatus.value === 'low'
                                          ? 'text-yellow-600 dark:text-yellow-400'
                                          : 'text-red-600 dark:text-red-400',
                                ]"
                            >
                                {{ seoAnalysis.keywordAnalysis.keywordDensity.value.toFixed(2) }}%
                                ({{ seoAnalysis.keywordAnalysis.totalOccurrences.value }} occurrences)
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                            Optimal: 1-2% density
                        </p>
                    </div>

                    <!-- Keyword Placement -->
                    <div class="rounded-md border border-neutral-200 dark:border-neutral-700">
                        <div class="border-b border-neutral-200 bg-neutral-50 px-4 py-2 dark:border-neutral-700 dark:bg-neutral-900/30">
                            <h4 class="text-sm font-medium text-neutral-900 dark:text-white">Keyword Placement</h4>
                        </div>
                        <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            <div
                                v-for="placement in seoAnalysis.keywordAnalysis.keywordPlacements.value"
                                :key="placement.location"
                                class="flex items-center justify-between px-4 py-2"
                            >
                                <span class="text-sm text-neutral-600 dark:text-neutral-300">
                                    {{ placement.location }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <span v-if="placement.count !== undefined" class="text-xs text-neutral-500">
                                        {{ placement.count }}
                                    </span>
                                    <svg
                                        v-if="placement.found"
                                        class="h-5 w-5 text-green-500"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="h-5 w-5 text-red-500"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prominence Score -->
                    <div class="rounded-md bg-blue-50 p-4 dark:bg-blue-900/20">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                Keyword Prominence Score
                            </span>
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                {{ seoAnalysis.keywordAnalysis.prominenceScore.value }}/100
                            </span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-blue-200 dark:bg-blue-900">
                            <div
                                class="h-full bg-blue-600 transition-all duration-500 dark:bg-blue-400"
                                :style="{ width: `${seoAnalysis.keywordAnalysis.prominenceScore.value}%` }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Middle Row: SEO Score (Left) and Recommendations (Right) -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- SEO Score Dashboard -->
            <SEOScoreDashboard
                :score="seoAnalysis.seoScore.value"
                :status="seoAnalysis.seoStatus.value"
                :high-priority-count="seoAnalysis.highPriorityCount.value"
                :medium-priority-count="seoAnalysis.mediumPriorityCount.value"
            />

            <!-- SEO Recommendations -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                <h3 class="mb-4 text-lg font-medium text-neutral-900 dark:text-white">Recommendations</h3>

                <div class="space-y-3">
                    <div
                        v-for="(rec, index) in seoAnalysis.recommendations.value.slice(0, 10)"
                        :key="index"
                        :class="[
                            'flex items-start gap-3 rounded-md p-3',
                            rec.priority === 'high'
                                ? 'bg-red-50 dark:bg-red-900/20'
                                : rec.priority === 'medium'
                                  ? 'bg-yellow-50 dark:bg-yellow-900/20'
                                  : 'bg-blue-50 dark:bg-blue-900/20',
                        ]"
                    >
                        <span
                            :class="[
                                'mt-0.5 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                rec.priority === 'high'
                                    ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'
                                    : rec.priority === 'medium'
                                      ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300'
                                      : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                            ]"
                        >
                            {{ rec.priority.toUpperCase() }}
                        </span>
                        <p
                            :class="[
                                'flex-1 text-sm',
                                rec.priority === 'high'
                                    ? 'text-red-800 dark:text-red-200'
                                    : rec.priority === 'medium'
                                      ? 'text-yellow-800 dark:text-yellow-200'
                                      : 'text-blue-800 dark:text-blue-200',
                            ]"
                        >
                            {{ rec.message }}
                        </p>
                    </div>

                    <div v-if="seoAnalysis.recommendations.value.length === 0" class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">All Good!</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                            No SEO improvements needed at this time.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Third Row: Content Quality (Left) and Readability (Right) -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Content Quality Metrics -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
            <h3 class="mb-4 text-lg font-medium text-neutral-900 dark:text-white">Content Quality</h3>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900/30">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white">
                        {{ seoAnalysis.contentMetrics.wordCount.value.toLocaleString() }}
                    </div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Words</div>
                    <div
                        class="mt-1 text-xs"
                        :class="
                            seoAnalysis.contentMetrics.wordCountStatus.value === 'optimal'
                                ? 'text-green-600 dark:text-green-400'
                                : 'text-yellow-600 dark:text-yellow-400'
                        "
                    >
                        {{ seoAnalysis.contentMetrics.wordCountStatus.value === 'optimal' ? 'Optimal' : 'Target: 1500-2500' }}
                    </div>
                </div>

                <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900/30">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white">
                        {{ seoAnalysis.contentMetrics.readingTime.value }}
                    </div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Min Read</div>
                </div>

                <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900/30">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white">
                        {{ seoAnalysis.contentMetrics.paragraphCount.value }}
                    </div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Paragraphs</div>
                </div>

                <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900/30">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white">
                        {{ seoAnalysis.contentMetrics.sentenceCount.value }}
                    </div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Sentences</div>
                </div>

                <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900/30">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white">
                        {{ seoAnalysis.contentMetrics.headingCount.value.total }}
                    </div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Headings</div>
                </div>

                <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900/30">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white">
                        {{ seoAnalysis.contentMetrics.imageCount.value }}
                    </div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Images</div>
                </div>

                <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900/30">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white">
                        {{ seoAnalysis.contentMetrics.linkCount.value }}
                    </div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Links</div>
                </div>

                <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900/30">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white">
                        {{ seoAnalysis.contentMetrics.avgSentenceLength.value }}
                    </div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Avg Words/Sentence</div>
                </div>
            </div>
        </div>

        <!-- Readability Analysis -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
            <h3 class="mb-4 text-lg font-medium text-neutral-900 dark:text-white">Readability</h3>

            <div class="space-y-4">
                <!-- Flesch Reading Ease -->
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                            Flesch Reading Ease
                        </span>
                        <span class="text-sm font-semibold text-neutral-900 dark:text-white">
                            {{ seoAnalysis.readabilityScore.fleschReadingEase.value.toFixed(1) }}/100
                        </span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
                        <div
                            class="h-full transition-all duration-500"
                            :class="
                                seoAnalysis.readabilityScore.fleschReadingEase.value >= 60
                                    ? 'bg-green-500'
                                    : seoAnalysis.readabilityScore.fleschReadingEase.value >= 50
                                      ? 'bg-yellow-500'
                                      : 'bg-red-500'
                            "
                            :style="{ width: `${seoAnalysis.readabilityScore.fleschReadingEase.value}%` }"
                        ></div>
                    </div>
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                        {{ seoAnalysis.readabilityScore.readingLevel.value }}
                    </p>
                </div>

                <!-- Grade Levels -->
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div class="rounded-md bg-neutral-50 p-3 text-center dark:bg-neutral-900/30">
                        <div class="text-lg font-bold text-neutral-900 dark:text-white">
                            {{ seoAnalysis.readabilityScore.scores.value.fleschKincaidGrade.toFixed(1) }}
                        </div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">Flesch-Kincaid</div>
                    </div>
                    <div class="rounded-md bg-neutral-50 p-3 text-center dark:bg-neutral-900/30">
                        <div class="text-lg font-bold text-neutral-900 dark:text-white">
                            {{ seoAnalysis.readabilityScore.scores.value.smogIndex.toFixed(1) }}
                        </div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">SMOG Index</div>
                    </div>
                    <div class="rounded-md bg-neutral-50 p-3 text-center dark:bg-neutral-900/30">
                        <div class="text-lg font-bold text-neutral-900 dark:text-white">
                            {{ seoAnalysis.readabilityScore.scores.value.colemanLiauIndex.toFixed(1) }}
                        </div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">Coleman-Liau</div>
                    </div>
                    <div class="rounded-md bg-neutral-50 p-3 text-center dark:bg-neutral-900/30">
                        <div class="text-lg font-bold text-neutral-900 dark:text-white">
                            {{ seoAnalysis.readabilityScore.averageGradeLevel.value.toFixed(1) }}
                        </div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">Avg Grade Level</div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <!-- End Third Row -->

        <!-- Bottom Row: URL Slug Analysis (Left Side Only) -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- URL Slug Analysis -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
            <h3 class="mb-4 text-lg font-medium text-neutral-900 dark:text-white">URL Slug Analysis</h3>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-neutral-600 dark:text-neutral-300">SEO Score</span>
                    <span
                        class="text-lg font-bold"
                        :class="
                            seoAnalysis.slugAnalysis.status.value === 'excellent'
                                ? 'text-green-600 dark:text-green-400'
                                : seoAnalysis.slugAnalysis.status.value === 'good'
                                  ? 'text-blue-600 dark:text-blue-400'
                                  : 'text-yellow-600 dark:text-yellow-400'
                        "
                    >
                        {{ seoAnalysis.slugAnalysis.score.value }}/100
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900/30">
                        <div class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Word Count</div>
                        <div
                            class="mt-1 text-lg font-bold"
                            :class="
                                seoAnalysis.slugAnalysis.wordCount.value >= 3 &&
                                seoAnalysis.slugAnalysis.wordCount.value <= 5
                                    ? 'text-green-600 dark:text-green-400'
                                    : 'text-yellow-600 dark:text-yellow-400'
                            "
                        >
                            {{ seoAnalysis.slugAnalysis.wordCount.value }} words
                        </div>
                    </div>
                    <div class="rounded-md bg-neutral-50 p-3 dark:bg-neutral-900/30">
                        <div class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Length</div>
                        <div
                            class="mt-1 text-lg font-bold"
                            :class="
                                seoAnalysis.slugAnalysis.length.value >= 30 &&
                                seoAnalysis.slugAnalysis.length.value <= 60
                                    ? 'text-green-600 dark:text-green-400'
                                    : 'text-yellow-600 dark:text-yellow-400'
                            "
                        >
                            {{ seoAnalysis.slugAnalysis.length.value }} chars
                        </div>
                    </div>
                </div>

                <div v-if="seoAnalysis.slugAnalysis.needsOptimization.value" class="rounded-md bg-blue-50 p-4 dark:bg-blue-900/20">
                    <div class="flex items-start">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <div class="ml-3 flex-1">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Optimized Suggestion</h4>
                            <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                <code class="rounded bg-blue-100 px-2 py-1 dark:bg-blue-900/40">
                                    {{ seoAnalysis.slugAnalysis.optimizedSlug.value }}
                                </code>
                            </p>
                            <button
                                type="button"
                                @click="$emit('apply-slug', seoAnalysis.slugAnalysis.optimizedSlug.value)"
                                class="mt-2 text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                Apply Suggestion
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <!-- End Bottom Row -->
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import SEOScoreDashboard from './SEOScoreDashboard.vue';
import type { useSEOAnalysis } from '@/composables/seo/useSEOAnalysis';

interface Props {
    seoAnalysis: ReturnType<typeof useSEOAnalysis>;
    targetKeyword: string;
}

interface Emits {
    (e: 'update:targetKeyword', value: string): void;
    (e: 'apply-slug', value: string): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Create a writable computed for v-model
const targetKeyword = computed({
    get: () => props.targetKeyword,
    set: (value: string) => emit('update:targetKeyword', value),
});
</script>
