<template>
    <div class="space-y-6" data-test="revision-history-tab">
        <!-- Header with Actions -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">
                    Revision History
                </h2>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    View, compare, and restore previous versions
                </p>
            </div>
            <button
                v-if="!loading"
                @click="fetchRevisions"
                data-test="refresh-revisions-button"
                class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
            >
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="rounded-lg bg-white p-12 shadow dark:bg-neutral-800" data-test="loading-state">
            <div class="flex flex-col items-center justify-center">
                <svg class="h-12 w-12 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-4 text-sm text-neutral-600 dark:text-neutral-400">Loading revisions...</p>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="revisions.length === 0" class="rounded-lg bg-white p-12 shadow dark:bg-neutral-800" data-test="empty-state">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-white">No Revisions Yet</h3>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Revisions will appear here after you save changes.
                </p>
            </div>
        </div>

        <!-- Revisions List -->
        <div v-else class="space-y-4" data-test="revisions-list">
            <div
                v-for="(revision, index) in revisions"
                :key="revision.id"
                class="rounded-lg border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-800"
                data-test="revision-item"
            >
                <div class="p-6">
                    <!-- Revision Header -->
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <!-- Current Badge -->
                                <span
                                    v-if="index === 0"
                                    class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400"
                                    data-test="current-version-badge"
                                >
                                    Current Version
                                </span>

                                <!-- Revision Number -->
                                <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400" data-test="revision-number">
                                    Revision #{{ revision.version || revision.id }}
                                </span>

                                <!-- Storage Driver Badge -->
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="storageDriverColor(revision.storage_driver || 'database')"
                                    data-test="storage-driver-badge"
                                >
                                    {{ revision.storage_driver || 'database' }}
                                </span>
                            </div>

                            <!-- Timestamp and Author -->
                            <div class="mt-2 flex items-center gap-4 text-sm text-neutral-600 dark:text-neutral-400">
                                <div class="flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span data-test="revision-timestamp">{{ formatTimestamp(revision.created_at || revision.timestamp) }}</span>
                                </div>

                                <div v-if="revision.author" class="flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span data-test="revision-author">{{ revision.author.name || revision.author }}</span>
                                </div>
                            </div>

                            <!-- Commit Message (for Git storage) -->
                            <div v-if="revision.commit_message" class="mt-2">
                                <p class="text-sm text-neutral-900 dark:text-white" data-test="commit-message">
                                    {{ revision.commit_message }}
                                </p>
                            </div>

                            <!-- SHA/Version ID -->
                            <div v-if="revision.sha || revision.version_id" class="mt-2">
                                <code class="rounded bg-neutral-100 px-2 py-1 text-xs font-mono text-neutral-700 dark:bg-neutral-900 dark:text-neutral-300" data-test="revision-sha">
                                    {{ revision.sha || revision.version_id }}
                                </code>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="ml-4 flex shrink-0 gap-2">
                            <button
                                @click="previewRevision(revision)"
                                data-test="preview-revision-button"
                                class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-1.5 text-sm font-medium text-neutral-700 hover:bg-neutral-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                            >
                                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Preview
                            </button>

                            <button
                                v-if="index !== 0"
                                @click="restoreRevision(revision)"
                                data-test="restore-revision-button"
                                class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                            >
                                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Restore
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div
            v-if="selectedRevision"
            class="fixed inset-0 z-50 overflow-y-auto"
            data-test="revision-preview-modal"
            @click.self="selectedRevision = null"
        >
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50 transition-opacity" @click="selectedRevision = null"></div>

                <div class="relative w-full max-w-4xl rounded-lg bg-white p-6 shadow-xl dark:bg-neutral-800">
                    <!-- Modal Header -->
                    <div class="mb-4 flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-white">
                                Revision Preview
                            </h3>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                {{ formatTimestamp(selectedRevision.created_at || selectedRevision.timestamp) }}
                            </p>
                        </div>
                        <button
                            @click="selectedRevision = null"
                            data-test="close-modal-x-button"
                            class="rounded-md text-neutral-400 hover:text-neutral-600 focus:outline-none dark:hover:text-neutral-300"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Content -->
                    <div class="max-h-[70vh] overflow-y-auto rounded-md border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-900" data-test="revision-content">
                        <div class="prose prose-neutral max-w-none dark:prose-invert">
                            <h1>{{ selectedRevision.title }}</h1>
                            <div v-if="selectedRevision.content_html" v-html="selectedRevision.content_html"></div>
                            <pre v-else-if="selectedRevision.markdown_content" class="whitespace-pre-wrap">{{ selectedRevision.markdown_content }}</pre>
                            <p v-else class="text-neutral-500">No content available</p>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            @click="selectedRevision = null"
                            data-test="close-modal-button"
                            class="rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                        >
                            Close
                        </button>
                        <button
                            @click="restoreRevision(selectedRevision); selectedRevision = null"
                            data-test="restore-from-modal-button"
                            class="rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                        >
                            Restore This Version
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

interface Revision {
    id: number | string;
    version?: number;
    version_id?: string;
    sha?: string;
    title?: string;
    markdown_content?: string;
    content_html?: string;
    commit_message?: string;
    storage_driver?: string;
    created_at?: string;
    timestamp?: string;
    author?: {
        id: number;
        name: string;
    } | string;
}

interface Props {
    contentId: number;
    contentType: 'post' | 'page';
}

const props = defineProps<Props>();

const revisions = ref<Revision[]>([]);
const loading = ref(true);
const selectedRevision = ref<Revision | null>(null);

const fetchRevisions = async () => {
    loading.value = true;

    try {
        // TODO: Replace with actual API endpoint when backend is ready
        // For now, show mock data
        setTimeout(() => {
            revisions.value = [];
            loading.value = false;
        }, 500);

        // Example API call (uncomment when backend is ready):
        // const response = await fetch(`/api/admin/${props.contentType}s/${props.contentId}/revisions`);
        // const data = await response.json();
        // revisions.value = data.revisions || [];
    } catch (error) {
        console.error('Failed to fetch revisions:', error);
        revisions.value = [];
    } finally {
        loading.value = false;
    }
};

const previewRevision = (revision: Revision) => {
    selectedRevision.value = revision;
};

const restoreRevision = (revision: Revision) => {
    if (!confirm('Are you sure you want to restore this revision? Current content will be replaced.')) {
        return;
    }

    // TODO: Implement restore functionality
    router.post(
        `/admin/${props.contentType}s/${props.contentId}/revisions/${revision.id}/restore`,
        {},
        {
            onSuccess: () => {
                alert('Revision restored successfully');
                fetchRevisions();
            },
            onError: (errors) => {
                console.error('Failed to restore revision:', errors);
                alert('Failed to restore revision');
            },
        }
    );
};

const formatTimestamp = (timestamp?: string) => {
    if (!timestamp) return 'Unknown date';
    const date = new Date(timestamp);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const storageDriverColor = (driver: string) => {
    switch (driver.toLowerCase()) {
        case 'git':
        case 'github':
        case 'gitlab':
        case 'bitbucket':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
        case 's3':
        case 'azure':
        case 'gcs':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        default:
            return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-400';
    }
};

onMounted(() => {
    fetchRevisions();
});
</script>
