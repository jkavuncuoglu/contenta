import axios from 'axios';

interface Page {
    id: number;
    title: string;
    slug: string;
    layout_id?: number;
    data?: any;
    status: 'draft' | 'published' | 'archived';
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
    published_at?: string;
    updated_at: string;
    layout?: Layout;
    author?: User;
}

interface Layout {
    id: number;
    name: string;
    slug: string;
    structure: any;
    description?: string;
    is_active: boolean;
}

interface Block {
    id: number;
    name: string;
    type: string;
    category: string;
    config_schema: any;
    component_path: string;
    preview_image?: string;
    description?: string;
    is_active: boolean;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export function usePageBuilderApi() {
    const baseUrl = '/admin/page-builder/api';

    // Pages API
    const fetchPages = async (params?: {
        page?: number;
        search?: string;
        status?: string;
        per_page?: number;
    }): Promise<{ data: PaginatedResponse<Page> }> => {
        const response = await axios.get(`${baseUrl}/pages`, { params });
        return response;
    };

    const fetchPage = async (id: number): Promise<{ data: Page }> => {
        const response = await axios.get(`${baseUrl}/pages/${id}`);
        return response;
    };

    const createPage = async (data: Partial<Page>): Promise<{ data: Page }> => {
        const response = await axios.post(`${baseUrl}/pages`, data);
        return response;
    };

    const updatePage = async (
        id: number,
        data: Partial<Page>,
    ): Promise<{ data: Page }> => {
        const response = await axios.put(`${baseUrl}/pages/${id}`, data);
        return response;
    };

    const deletePage = async (id: number): Promise<void> => {
        await axios.delete(`${baseUrl}/pages/${id}`);
    };

    const publishPage = async (id: number): Promise<{ data: Page }> => {
        const response = await axios.post(`${baseUrl}/pages/${id}/publish`);
        return response;
    };

    const unpublishPage = async (id: number): Promise<{ data: Page }> => {
        const response = await axios.post(`${baseUrl}/pages/${id}/unpublish`);
        return response;
    };

    const duplicatePage = async (id: number): Promise<{ data: Page }> => {
        const response = await axios.post(`${baseUrl}/pages/${id}/duplicate`);
        return response;
    };

    const previewPage = async (
        id: number,
    ): Promise<{ data: { html: string } }> => {
        const response = await axios.get(`${baseUrl}/pages/${id}/preview`);
        return response;
    };

    // Layouts API
    const fetchLayouts = async (params?: {
        active_only?: boolean;
        search?: string;
    }): Promise<{ data: Layout[] }> => {
        const response = await axios.get(`${baseUrl}/layouts`, { params });
        return response;
    };

    const fetchLayout = async (id: number): Promise<{ data: Layout }> => {
        const response = await axios.get(`${baseUrl}/layouts/${id}`);
        return response;
    };

    const createLayout = async (
        data: Partial<Layout>,
    ): Promise<{ data: Layout }> => {
        const response = await axios.post(`${baseUrl}/layouts`, data);
        return response;
    };

    const updateLayout = async (
        id: number,
        data: Partial<Layout>,
    ): Promise<{ data: Layout }> => {
        const response = await axios.put(`${baseUrl}/layouts/${id}`, data);
        return response;
    };

    const deleteLayout = async (id: number): Promise<void> => {
        await axios.delete(`${baseUrl}/layouts/${id}`);
    };

    // Blocks API
    const fetchBlocks = async (params?: {
        active_only?: boolean;
        category?: string;
        search?: string;
    }): Promise<{ data: Block[] }> => {
        const response = await axios.get(`${baseUrl}/blocks`, { params });
        return response;
    };

    const fetchBlock = async (id: number): Promise<{ data: Block }> => {
        const response = await axios.get(`${baseUrl}/blocks/${id}`);
        return response;
    };

    const createBlock = async (
        data: Partial<Block>,
    ): Promise<{ data: Block }> => {
        const response = await axios.post(`${baseUrl}/blocks`, data);
        return response;
    };

    const updateBlock = async (
        id: number,
        data: Partial<Block>,
    ): Promise<{ data: Block }> => {
        const response = await axios.put(`${baseUrl}/blocks/${id}`, data);
        return response;
    };

    const deleteBlock = async (id: number): Promise<void> => {
        await axios.delete(`${baseUrl}/blocks/${id}`);
    };

    const fetchBlockCategories = async (): Promise<{
        data: Record<string, string>;
    }> => {
        const response = await axios.get(`${baseUrl}/blocks-categories`);
        return response;
    };

    const validateBlockConfig = async (
        blockId: number,
        config: any,
    ): Promise<{
        data: { valid: boolean; errors: Record<string, string> };
    }> => {
        const response = await axios.post(
            `${baseUrl}/blocks/${blockId}/validate-config`,
            { config },
        );
        return response;
    };

    return {
        // Pages
        fetchPages,
        fetchPage,
        createPage,
        updatePage,
        deletePage,
        publishPage,
        unpublishPage,
        duplicatePage,
        previewPage,

        // Layouts
        fetchLayouts,
        fetchLayout,
        createLayout,
        updateLayout,
        deleteLayout,

        // Blocks
        fetchBlocks,
        fetchBlock,
        createBlock,
        updateBlock,
        deleteBlock,
        fetchBlockCategories,
        validateBlockConfig,
    };
}
