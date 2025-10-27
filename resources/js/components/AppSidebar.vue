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
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import dashboard from '@/routes/admin/dashboard';
import posts from '@/routes/admin/posts';
import pages from '@/routes/admin/pages';
import categories from '@/routes/admin/categories';
import tags from '@/routes/admin/tags';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
        icon: 'material-symbols-light:bar-chart',
    },
    {
        title: 'Content',
        redirect: pages.index().url,
        icon: 'material-symbols-light:ad-group-outline-sharp',
        children: [
            {
                title: 'Posts',
                href: posts.index().url,
                icon: 'material-symbols-light:insert-text',
            },
            {
                title: 'Pages',
                href: pages.index().url,
                icon: 'material-symbols-light:web',
            },
            {
                title: 'Categories',
                href: categories.index().url,
                icon: 'material-symbols-light:category',
            },
            {
                title: 'Tags',
                href: tags.index().url,
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
        href: '/admin/settings/site',
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
        <SidebarHeader class="pb-2 border-b">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard.index().url" prefetch>
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="pt-4">
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
