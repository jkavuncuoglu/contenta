import { ref, watch, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

interface AutoSaveOptions {
    url: string;
    data: () => Record<string, any>;
    debounceMs?: number;
    enabled?: () => boolean;
    onSuccess?: () => void;
    onError?: (error: any) => void;
}

/**
 * Auto-save composable with debounced save functionality
 *
 * @param options Configuration options
 * @returns Auto-save state and control functions
 *
 * @example
 * const { isSaving, lastSaved, saveNow } = useAutoSave({
 *   url: `/admin/posts/${props.post.id}/autosave`,
 *   data: () => ({ content_markdown: form.content_markdown }),
 *   debounceMs: 5000,
 *   enabled: () => form.status === 'draft',
 * });
 */
export function useAutoSave(options: AutoSaveOptions) {
    const {
        url,
        data,
        debounceMs = 5000,
        enabled = () => true,
        onSuccess,
        onError,
    } = options;

    const isSaving = ref(false);
    const lastSaved = ref<Date | null>(null);
    const error = ref<string | null>(null);
    let debounceTimer: ReturnType<typeof setTimeout> | null = null;

    /**
     * Perform the actual save operation
     */
    const performSave = async () => {
        if (!enabled()) {
            return;
        }

        isSaving.value = true;
        error.value = null;

        try {
            await new Promise<void>((resolve, reject) => {
                router.post(
                    url,
                    data(),
                    {
                        preserveScroll: true,
                        preserveState: true,
                        only: [], // Don't reload any props
                        onSuccess: () => {
                            lastSaved.value = new Date();
                            if (onSuccess) {
                                onSuccess();
                            }
                            resolve();
                        },
                        onError: (err) => {
                            error.value = 'Auto-save failed';
                            if (onError) {
                                onError(err);
                            }
                            reject(err);
                        },
                        onFinish: () => {
                            isSaving.value = false;
                        },
                    }
                );
            });
        } catch (err) {
            console.error('Auto-save error:', err);
        }
    };

    /**
     * Schedule a debounced save
     */
    const scheduleSave = () => {
        if (!enabled()) {
            return;
        }

        // Clear existing timer
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }

        // Schedule new save
        debounceTimer = setTimeout(() => {
            performSave();
        }, debounceMs);
    };

    /**
     * Save immediately without debouncing
     */
    const saveNow = async () => {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
            debounceTimer = null;
        }
        await performSave();
    };

    /**
     * Cancel any pending save
     */
    const cancelSave = () => {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
            debounceTimer = null;
        }
    };

    /**
     * Format last saved time for display
     */
    const formatLastSaved = (): string => {
        if (!lastSaved.value) {
            return 'Never';
        }

        const now = new Date();
        const diff = Math.floor((now.getTime() - lastSaved.value.getTime()) / 1000);

        if (diff < 60) {
            return 'Just now';
        } else if (diff < 3600) {
            const minutes = Math.floor(diff / 60);
            return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        } else if (diff < 86400) {
            const hours = Math.floor(diff / 3600);
            return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        } else {
            return lastSaved.value.toLocaleString();
        }
    };

    // Cleanup on unmount
    onUnmounted(() => {
        cancelSave();
    });

    return {
        isSaving,
        lastSaved,
        error,
        scheduleSave,
        saveNow,
        cancelSave,
        formatLastSaved,
    };
}

/**
 * Watch reactive data and trigger auto-save on changes
 *
 * @example
 * const autoSave = useAutoSave({ ... });
 * watchAutoSave(autoSave, () => form.content_markdown);
 */
export function watchAutoSave(
    autoSave: ReturnType<typeof useAutoSave>,
    source: () => any
) {
    watch(
        source,
        () => {
            autoSave.scheduleSave();
        },
        { deep: true }
    );
}
