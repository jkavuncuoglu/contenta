import type { IconifyIcon } from '@iconify/vue';
import { InertiaLinkProps } from '@inertiajs/vue3';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href?: InertiaLinkProps['href'];
    redirect?: string;
    icon?: IconifyIcon | string;
    children?: NavItem[];
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    username?: string;
    bio?: string;
    avatar?: string;
    timezone?: string;
    language?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Permission {
    id: number;
    name: string;
    guard_name: string;
    created_at: string | null;
    updated_at: string | null;
}

export interface Role {
    id: number;
    name: string;
    guard_name: string;
    users_count?: number;
    permissions?: Permission[];
    created_at: string | null;
    updated_at: string | null;
}

export interface PostForm {
    title: string;
    slug: string;
    content_markdown: string;
    content_html?: string;
    table_of_contents?: Array<{ level: number; text: string; id: string }>;
    excerpt?: string;
    status: 'draft' | 'published' | 'scheduled' | 'private';
    published_at?: string;
    categories?: number[];
    tags: string[];
    custom_fields?: Record<string, unknown>;
    storage_driver?: 'database' | 'local' | 's3' | 'azure' | 'gcs' | 'github' | 'gitlab' | 'bitbucket';
    commit_message?: string;
}

export interface Post {
    id: number;
    title: string;
    slug: string;
    content_markdown: string;
    content_html?: string;
    table_of_contents?: Array<{ level: number; text: string; id: string }>;
    excerpt?: string;
    status: 'draft' | 'published' | 'scheduled' | 'private';
    published_at?: string;
    author_id: number;
    author?: User;
    categories?: any[];
    tags?: any[];
    created_at: string;
    updated_at: string;
}
