import { router as inertia } from '@inertiajs/vue3';
import { ref } from 'vue';

export function useTagsStore() {
    const tags = ref<any[]>([]);
    const isLoading = ref(false);
    const hasError = ref(false);
    const error = ref<string | null>(null);
    const hasTags = ref(false);
    const totalTags = ref(0);
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
    });

    const fetchTags = async (filters: any = {}) => {
        isLoading.value = true;
        hasError.value = false;
        error.value = null;

        return new Promise((resolve) => {
            try {
                inertia.get(
                    '/admin/tags',
                    { params: filters },
                    {
                        preserveState: true,
                        onSuccess: (page: any) => {
                            const res =
                                (page.props &&
                                    (page.props.tags ??
                                        page.props.data ??
                                        page.props)) ??
                                {};
                            const data = res.data ?? res ?? [];
                            tags.value = Array.isArray(data)
                                ? data
                                : (data.data ?? []);
                            totalTags.value =
                                res.total ??
                                res.meta?.total ??
                                tags.value.length;
                            pagination.value = res.meta ?? {
                                current_page: 1,
                                last_page: 1,
                                from: 1,
                                to: tags.value.length,
                                total: tags.value.length,
                            };
                            hasTags.value = tags.value.length > 0;
                            resolve({ success: true, data: tags.value });
                        },
                        onError: (page: any) => {
                            hasError.value = true;
                            const msg =
                                (page.props &&
                                    (page.props.flash?.error ||
                                        page.props.error ||
                                        page.props.message)) ??
                                'Failed to load tags';
                            error.value =
                                typeof msg === 'string'
                                    ? msg
                                    : JSON.stringify(msg);
                            resolve({ success: false, error: error.value });
                        },
                    },
                );
            } catch (e: any) {
                hasError.value = true;
                error.value = e?.message || 'Failed to load tags';
                resolve({ success: false, error: error.value });
            } finally {
                isLoading.value = false;
            }
        });
    };

    const deleteTag = async (id: number) => {
        return new Promise((resolve) => {
            try {
                inertia.delete(
                    `/admin/tags/${id}`,
                    {},
                    {
                        preserveState: true,
                        onSuccess: () => resolve({ success: true }),
                        onError: (page: any) => {
                            const msg =
                                (page.props &&
                                    (page.props.flash?.error ||
                                        page.props.error ||
                                        page.props.message)) ??
                                'Failed to delete tag';
                            error.value =
                                typeof msg === 'string'
                                    ? msg
                                    : JSON.stringify(msg);
                            resolve({ success: false, error: error.value });
                        },
                    },
                );
            } catch (e: any) {
                error.value = e?.message || 'Failed to delete tag';
                resolve({ success: false, error: error.value });
            }
        });
    };

    const bulkAction = async (
        action: string,
        ids: number[],
        targetId?: number,
    ) => {
        return new Promise((resolve) => {
            try {
                const payload: any = { ids };
                if (targetId) payload.target = targetId;
                inertia.post(`/admin/tags/bulk/${action}`, payload, {
                    preserveState: true,
                    onSuccess: () => resolve({ success: true }),
                    onError: (page: any) => {
                        const msg =
                            (page.props &&
                                (page.props.flash?.error ||
                                    page.props.error ||
                                    page.props.message)) ??
                            'Bulk action failed';
                        error.value =
                            typeof msg === 'string' ? msg : JSON.stringify(msg);
                        resolve({ success: false, error: error.value });
                    },
                });
            } catch (e: any) {
                error.value = e?.message || 'Bulk action failed';
                resolve({ success: false, error: error.value });
            }
        });
    };

    return {
        tags,
        isLoading,
        hasError,
        error,
        hasTags,
        totalTags,
        pagination,
        fetchTags,
        deleteTag,
        bulkAction,
    };
}

export default useTagsStore;
