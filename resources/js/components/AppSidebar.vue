`<script setup lang="ts">
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
        title: 'Dashboard',
        href: '/admin/dashboard',
        icon: 'material-symbols-light:bar-chart',
    },
    {
        title: 'Content',
        redirect: '/admin/posts',
        icon: 'material-symbols-light:ad-group-outline-sharp',
        children: [
            {
                title: 'Pages',
                href: '/admin/pages',
                icon: 'material-symbols-light:web',
            },
            {
                title: 'Posts',
                href: '/admin/posts',
                icon: 'material-symbols-light:insert-text',
            },
            {
                title: 'Categories',
                href: '/admin/categories',
                icon: 'material-symbols-light:category',
            },
            {
                title: 'Tags',
                href: '/admin/tags',
                icon: 'material-symbols-light:tag',
            },
        ],
    },
    {
        title: 'Media',
        href: '/admin/content/media',
        icon: 'material-symbols-light:perm-media',
    },
    {
        title: 'Comments',
        href: '/admin/content/comments',
        icon: 'material-symbols-light:comment',
    },
    {
        title: 'Settings',
        href: '/admin/settings',
        icon: 'material-symbols-light:settings',
        children: [
            {
                title: 'Site Settings',
                href: '/admin/settings/site',
                icon: 'material-symbols-light:web',
            },
            {
                title: 'Security',
                href: '/admin/settings/security',
                icon: 'material-symbols-light:shield',
            },
            {
                title: 'Users',
                href: '/admin/settings/users',
                icon: 'lucide:users-round',
            },
            {
                title: 'Roles & Permissions',
                href: '/admin/settings/permissions',
                icon: 'material-symbols-light:lock',
            },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: 'material-symbols-light:folder',
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: 'material-symbols-light:bookOpen',
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
            <NavMain :items="mainNavItems" :level="0" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
`
