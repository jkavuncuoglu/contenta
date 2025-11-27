import { router as inertia } from '@inertiajs/vue3';

const wrapResponse = (payload: any) => ({ data: payload });

export const api = {
    get(url: string, config: any = {}) {
        const params = config.params ?? {};
        return new Promise((resolve, reject) => {
            try {
                inertia.get(url, params, {
                    preserveState: config.preserveState ?? true,
                    onSuccess: (page: any) => {
                        const res =
                            (page.props && (page.props.data ?? page.props)) ??
                            {};
                        resolve(wrapResponse(res));
                    },
                    onError: (page: any) => reject(page),
                });
            } catch (e) {
                reject(e);
            }
        });
    },

    post(url: string, data: any = {}, config: any = {}) {
        return new Promise((resolve, reject) => {
            try {
                inertia.post(url, data, {
                    preserveState: config.preserveState ?? true,
                    onSuccess: (page: any) => {
                        const res =
                            (page.props && (page.props.data ?? page.props)) ??
                            {};
                        resolve(wrapResponse(res));
                    },
                    onError: (page: any) => reject(page),
                });
            } catch (e) {
                reject(e);
            }
        });
    },

    put(url: string, data: any = {}, config: any = {}) {
        return new Promise((resolve, reject) => {
            try {
                inertia.put(url, data, {
                    preserveState: config.preserveState ?? true,
                    onSuccess: (page: any) => {
                        const res =
                            (page.props && (page.props.data ?? page.props)) ??
                            {};
                        resolve(wrapResponse(res));
                    },
                    onError: (page: any) => reject(page),
                });
            } catch (e) {
                reject(e);
            }
        });
    },

    delete(url: string, config: any = {}) {
        return new Promise((resolve, reject) => {
            try {
                inertia.delete(
                    url,
                    {},
                    {
                        preserveState: config.preserveState ?? true,
                        onSuccess: (page: any) => {
                            const res =
                                (page.props &&
                                    (page.props.data ?? page.props)) ??
                                {};
                            resolve(wrapResponse(res));
                        },
                        onError: (page: any) => reject(page),
                    },
                );
            } catch (e) {
                reject(e);
            }
        });
    },
};

export default api;
