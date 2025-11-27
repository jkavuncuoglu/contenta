<template>
    <div class="page-revisions w-full">
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="text-center">
                <div
                    class="inline-block h-8 w-8 animate-spin rounded-full border-b-2 border-blue-600"
                ></div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Loading revisions...
                </p>
            </div>
        </div>

        <!-- Revisions Content -->
        <div v-else-if="revisions.length > 0" class="w-full space-y-6">
            <!-- Revision Timeline Slider -->
            <div
                class="revision-slider w-full rounded-lg border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-900"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h4
                            class="text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Revision Timeline
                        </h4>
                        <p
                            class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                        >
                            {{ displayedRevisions.length }}
                            {{
                                displayedRevisions.length === 1
                                    ? 'revision'
                                    : 'revisions'
                            }}
                            <span
                                v-if="
                                    !showAllRevisions && revisions.length > 10
                                "
                            >
                                (showing oldest to newest)</span
                            >
                        </p>
                    </div>
                    <button
                        v-if="revisions.length > 10"
                        @click="toggleShowAll"
                        class="rounded-md border border-gray-300 px-3 py-1.5 text-xs text-gray-700 transition-colors hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        {{
                            showAllRevisions
                                ? 'Show Latest 10'
                                : `Show All ${revisions.length}`
                        }}
                    </button>
                </div>

                <!-- Slider -->
                <div class="relative py-4">
                    <input
                        type="range"
                        v-model="selectedRevisionIndex"
                        :min="0"
                        :max="displayedRevisions.length - 1"
                        class="slider h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200 dark:bg-gray-700"
                        @input="onSliderChange"
                    />
                    <!-- Revision Points -->
                    <div class="mt-2 flex justify-between px-1">
                        <div
                            v-for="(revision, index) in displayedRevisions"
                            :key="revision.id"
                            class="flex flex-col items-center"
                            :style="{
                                width: `${100 / displayedRevisions.length}%`,
                            }"
                        >
                            <div
                                class="h-3 w-3 rounded-full transition-all"
                                :class="
                                    index === selectedRevisionIndex
                                        ? 'scale-125 bg-blue-600 ring-4 ring-blue-200 dark:ring-blue-900'
                                        : 'bg-gray-400 dark:bg-gray-600'
                                "
                            ></div>
                            <span
                                class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                >#{{ revision.revision_number }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revision Preview -->
            <div
                v-if="selectedRevisionData"
                class="revision-preview w-full overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
            >
                <div
                    class="border-b border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="mb-3 flex items-center space-x-3">
                                <h4
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                >
                                    Preview: Revision #{{
                                        selectedRevision?.revision_number
                                    }}
                                </h4>
                            </div>
                            <div v-if="selectedRevision" class="space-y-2">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900"
                                    >
                                        <span
                                            class="text-xs font-semibold text-blue-600 dark:text-blue-300"
                                            >#{{
                                                selectedRevision.revision_number
                                            }}</span
                                        >
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h5
                                            class="truncate text-sm font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ selectedRevision.title }}
                                        </h5>
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <span
                                                class="text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                by {{ selectedRevision.user }}
                                            </span>
                                            <span
                                                class="text-gray-300 dark:text-gray-600"
                                                >•</span
                                            >
                                            <span
                                                class="text-xs text-gray-500 dark:text-gray-400"
                                                :title="
                                                    selectedRevision.created_at
                                                "
                                            >
                                                {{
                                                    selectedRevision.created_at_human
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <p
                                    v-if="selectedRevision.reason"
                                    class="ml-11 text-xs text-gray-600 italic dark:text-gray-400"
                                >
                                    {{ selectedRevision.reason }}
                                </p>
                            </div>
                        </div>
                        <button
                            v-if="selectedRevision"
                            @click="restoreRevision(selectedRevision)"
                            :disabled="restoring === selectedRevision.id"
                            class="ml-4 inline-flex flex-shrink-0 items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <svg
                                v-if="restoring !== selectedRevision.id"
                                class="mr-2 h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                ></path>
                            </svg>
                            <div
                                v-else
                                class="mr-2 inline-block h-4 w-4 animate-spin rounded-full border-b-2 border-white"
                            ></div>
                            {{
                                restoring === selectedRevision.id
                                    ? 'Restoring...'
                                    : 'Restore This Version'
                            }}
                        </button>
                    </div>
                </div>
                <div
                    class="max-h-96 overflow-auto bg-gray-50 p-6 dark:bg-gray-900"
                >
                    <div
                        class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="origin-top scale-90 transform">
                            <BlockRenderer
                                v-for="section in selectedRevisionData.data
                                    ?.sections || []"
                                :key="section.id"
                                :section="section"
                                :available-blocks="availableBlocks"
                                :preview-mode="true"
                            />
                            <div
                                v-if="
                                    !selectedRevisionData.data?.sections?.length
                                "
                                class="p-12 text-center text-gray-500 dark:text-gray-400"
                            >
                                <p class="text-sm">
                                    This revision has no content blocks
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="py-12 text-center">
            <svg
                class="mx-auto h-12 w-12 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                ></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                No revisions yet
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Revisions will appear here as you save changes to the page.
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import BlockRenderer from './BlockRenderer.vue';

interface Revision {
    id: number;
    revision_number: number;
    title: string;
    user: string;
    reason?: string;
    created_at: string;
    created_at_human: string;
}

interface RevisionData {
    id: number;
    revision_number: number;
    title: string;
    slug: string;
    data: {
        sections: any[];
    };
    status: string;
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
    schema_data?: any;
}

interface Props {
    pageId: number;
    availableBlocks: any[];
}

const props = defineProps<Props>();

const revisions = ref<Revision[]>([]);
const loading = ref(true);
const restoring = ref<number | null>(null);
const showAllRevisions = ref(false);
const selectedRevisionIndex = ref(0);
const selectedRevisionData = ref<RevisionData | null>(null);
const loadingRevisionData = ref(false);

// Computed property for displayed revisions (limited to 10 unless "Show All" is clicked)
// Reversed to show oldest first (left) to newest last (right)
const displayedRevisions = computed(() => {
    let revs = [];
    if (showAllRevisions.value || revisions.value.length <= 10) {
        revs = revisions.value;
    } else {
        revs = revisions.value.slice(0, 10);
    }
    // Reverse to show oldest (left) to newest (right)
    return [...revs].reverse();
});

// Computed property for selected revision
const selectedRevision = computed(() => {
    return displayedRevisions.value[selectedRevisionIndex.value] || null;
});

const fetchRevisions = async () => {
    try {
        loading.value = true;
        const response = await fetch(
            `/admin/page-builder/api/pages/${props.pageId}/revisions`,
            {
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            },
        );

        if (response.ok) {
            revisions.value = await response.json();
            // Select the last (most recent) revision by default - now on the right side
            if (revisions.value.length > 0) {
                selectedRevisionIndex.value =
                    displayedRevisions.value.length - 1;
                await fetchRevisionData(
                    displayedRevisions.value[
                        displayedRevisions.value.length - 1
                    ].id,
                );
            }
        } else {
            console.error('Failed to fetch revisions');
        }
    } catch (error) {
        console.error('Error fetching revisions:', error);
    } finally {
        loading.value = false;
    }
};

const fetchRevisionData = async (revisionId: number) => {
    try {
        loadingRevisionData.value = true;
        const response = await fetch(
            `/admin/page-builder/api/pages/${props.pageId}/revisions/${revisionId}`,
            {
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            },
        );

        if (response.ok) {
            selectedRevisionData.value = await response.json();
        } else {
            console.error('Failed to fetch revision data');
            selectedRevisionData.value = null;
        }
    } catch (error) {
        console.error('Error fetching revision data:', error);
        selectedRevisionData.value = null;
    } finally {
        loadingRevisionData.value = false;
    }
};

const onSliderChange = () => {
    const revision = selectedRevision.value;
    if (revision) {
        fetchRevisionData(revision.id);
    }
};

const toggleShowAll = () => {
    showAllRevisions.value = !showAllRevisions.value;
    // If we're hiding revisions, make sure the selected index is within bounds
    if (!showAllRevisions.value && selectedRevisionIndex.value >= 10) {
        // Select the last item (newest, rightmost)
        selectedRevisionIndex.value = displayedRevisions.value.length - 1;
        const lastRevision =
            displayedRevisions.value[displayedRevisions.value.length - 1];
        if (lastRevision) {
            fetchRevisionData(lastRevision.id);
        }
    }
};

const restoreRevision = async (revision: Revision) => {
    if (
        !confirm(
            `Are you sure you want to restore to revision #${revision.revision_number}? This will create a new revision with the restored content.`,
        )
    ) {
        return;
    }

    try {
        restoring.value = revision.id;
        const response = await fetch(
            `/admin/page-builder/api/pages/${props.pageId}/revisions/${revision.id}/restore`,
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
            },
        );

        if (response.ok) {
            // Reload the page to show the restored content
            router.reload();
        } else {
            const error = await response.json();
            alert(
                'Failed to restore revision: ' +
                    (error.error || 'Unknown error'),
            );
        }
    } catch (error) {
        console.error('Error restoring revision:', error);
        alert('Failed to restore revision');
    } finally {
        restoring.value = null;
    }
};

onMounted(() => {
    fetchRevisions();
});

// Expose method to refresh revisions from parent
defineExpose({
    fetchRevisions,
});
</script>

<style scoped>
.revision-item {
    transition: all 0.2s ease;
}

.revision-item:hover {
    transform: translateY(-1px);
}

/* Custom slider styling */
.slider {
    -webkit-appearance: none;
    appearance: none;
}

.slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #2563eb;
    cursor: pointer;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.slider::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #2563eb;
    cursor: pointer;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.slider::-webkit-slider-thumb:hover {
    background: #1d4ed8;
    transform: scale(1.1);
}

.slider::-moz-range-thumb:hover {
    background: #1d4ed8;
    transform: scale(1.1);
}
</style>
