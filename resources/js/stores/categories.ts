import { router as inertia } from '@inertiajs/vue3';
import { ref } from 'vue';

export function useCategoriesStore() {
    const categories = ref<any[]>([]);
    const loading = ref(false);
    const error = ref<string | null>(null);

    const fetchCategories = (params: any = {}) => {
        loading.value = true;
        error.value = null;

        return new Promise((resolve) => {
            try {
                inertia.get('/admin/categories', params, {
                    preserveState: true,
                    onSuccess: (page: any) => {
                        const res =
                            (page.props &&
                                (page.props.categories ??
                                    page.props.data ??
                                    page.props)) ??
                            {};
                        const data = res.data ?? res ?? [];
                        categories.value = Array.isArray(data)
                            ? data
                            : (data.data ?? []);
                        resolve({ success: true, data: categories.value });
                    },
                    onError: (page: any) => {
                        const msg =
                            (page.props &&
                                (page.props.flash?.error ||
                                    page.props.error ||
                                    page.props.message)) ??
                            'Failed to load categories';
                        error.value =
                            typeof msg === 'string' ? msg : JSON.stringify(msg);
                        resolve({ success: false, error: error.value });
                    },
                });
            } catch (e: any) {
                error.value = e?.message || 'Failed to load categories';
                resolve({ success: false, error: error.value });
            } finally {
                loading.value = false;
            }
        });
    };

    const createCategory = async (payload: any) => {
        loading.value = true;
        error.value = null;

        return new Promise((resolve) => {
            try {
                inertia.post('/admin/categories', payload, {
                    preserveState: true,
                    onSuccess: (page: any) => {
                        const res =
                            (page.props &&
                                (page.props.category ??
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
                            'Failed to create category';
                        error.value =
                            typeof msg === 'string' ? msg : JSON.stringify(msg);
                        resolve({ success: false, error: error.value });
                    },
                });
            } catch (e: any) {
                error.value = e?.message || 'Failed to create category';
                resolve({ success: false, error: error.value });
            } finally {
                loading.value = false;
            }
        });
    };

    const updateCategory = async (id: number | string, payload: any) => {
        loading.value = true;
        error.value = null;

        return new Promise((resolve) => {
            try {
                inertia.put(`/admin/categories/${id}`, payload, {
                    preserveState: true,
                    onSuccess: (page: any) => {
                        const res =
                            (page.props &&
                                (page.props.category ??
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
                            'Failed to update category';
                        error.value =
                            typeof msg === 'string' ? msg : JSON.stringify(msg);
                        resolve({ success: false, error: error.value });
                    },
                });
            } catch (e: any) {
                error.value = e?.message || 'Failed to update category';
                resolve({ success: false, error: error.value });
            } finally {
                loading.value = false;
            }
        });
    };

    return {
        categories,
        loading,
        error,
        fetchCategories,
        createCategory,
        updateCategory,
    };
}

export default useCategoriesStore;
