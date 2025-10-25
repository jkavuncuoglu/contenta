import { InertiaLinkProps } from '@inertiajs/vue3';
import type { IconifyIcon } from '@iconify/vue';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
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
