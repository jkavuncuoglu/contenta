<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Administration',
        href: '/admin',
        redirect: '/admin/dashboard',
        children: [
            {
                title: 'Dashboard',
                href: '/admin/dashboard',
                icon: 'barChart3',
            },
            {
                title: 'Posts',
                href: '/admin/posts',
                icon: 'fileText',
            },
            {
                title: 'Categories',
                href: '/admin/categories',
                icon: 'folderOpen',
            },
            {
                title: 'Tags',
                href: '/admin/tags',
                icon: 'tag',
            },
            {
                title: 'Media',
                href: '/admin/content/media',
                icon: 'image',
            },
            {
                title: 'Comments',
                href: '/admin/content/comments',
                icon: 'messageSquare',
            },
            {
                title: 'Settings',
                href: '/admin/settings',
                icon: 'settings',
                children: [
                    {
                        title: 'Site Settings',
                        href: '/admin/settings/site',
                        icon: 'settings',
                    },
                    {
                        title: 'Security',
                        href: '/admin/settings/security',
                        icon: 'shield',
                    },
                    {
                        title: 'Users',
                        href: '/admin/settings/users',
                        icon: 'users',
                    },
                    {
                        title: 'Roles & Permissions',
                        href: '/admin/settings/permissions',
                        icon: 'lock',
                    },
                ],
            },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: 'folder',
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: 'bookOpen',
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
