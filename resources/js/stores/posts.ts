import { router as inertia } from '@inertiajs/vue3';
import { ref } from 'vue';

interface StoreResult<T = any> {
    success: boolean;
    data?: T;
    error?: string;
}

export function usePostsStore() {
    const posts = ref<any[]>([]);
    const currentPost = ref<any | null>(null);
    const loading = ref(false);
    const error = ref<string | null>(null);

    const fetchPost = async (id: number): Promise<StoreResult> => {
        loading.value = true;
        error.value = null;

        return new Promise((resolve) => {
            try {
                inertia.get(
                    `/admin/posts/${id}`,
                    {},
                    {
                        preserveState: true,
                        onSuccess: (page: any) => {
                            const res =
                                (page.props &&
                                    (page.props.post ??
                                        page.props.data ??
                                        page.props)) ??
                                {};
                            currentPost.value = res.data ?? res ?? null;
                            resolve({ success: true, data: currentPost.value });
                        },
                        onError: (page: any) => {
                            const msg =
                                (page.props &&
                                    (page.props.flash?.error ||
                                        page.props.error ||
                                        page.props.message)) ??
                                'Failed to fetch post';
                            error.value =
                                typeof msg === 'string'
                                    ? msg
                                    : JSON.stringify(msg);
                            resolve({ success: false, error: error.value });
                        },
                    },
                );
            } catch (e: any) {
                error.value = e?.message || 'Failed to fetch post';
                resolve({ success: false, error: error.value });
            } finally {
                loading.value = false;
            }
        });
    };

    const createPost = async (payload: any): Promise<StoreResult> => {
        loading.value = true;
        error.value = null;

        return new Promise((resolve) => {
            try {
                inertia.post('/admin/posts', payload, {
                    preserveState: true,
                    onSuccess: (page: any) => {
                        const res =
                            (page.props &&
                                (page.props.post ??
                                    page.props.data ??
                                    page.props)) ??
                            {};
                        resolve({ success: true, data: res.data ?? res });
                    },
                    onError: (page: any) => {
                        const msg =
                            (page.props &&
                                (page.props.flash?.error ||
                                    page.props.error ||
                                    page.props.message)) ??
                            'Failed to create post';
                        error.value =
                            typeof msg === 'string' ? msg : JSON.stringify(msg);
                        resolve({ success: false, error: error.value });
                    },
                });
            } catch (e: any) {
                const msg = e?.message ?? 'Failed to create post';
                error.value =
                    typeof msg === 'string' ? msg : JSON.stringify(msg);
                resolve({ success: false, error: error.value });
            } finally {
                loading.value = false;
            }
        });
    };

    return {
        posts,
        currentPost,
        loading,
        error,
        fetchPost,
        createPost,
    };
}

export default usePostsStore;
