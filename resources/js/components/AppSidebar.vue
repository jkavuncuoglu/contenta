`
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
import categories from '@/routes/admin/categories';
import dashboard from '@/routes/admin/dashboard';
import pages from '@/routes/admin/pages';
import posts from '@/routes/admin/posts';
import tags from '@/routes/admin/tags';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';

const props = usePage().props;

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
        icon: 'material-symbols-light:bar-chart',
    },
    {
        title: 'Pages',
        href: pages.index().url,
        icon: 'material-symbols-light:view-quilt',
    },
    {
        title: 'Blog',
        href: posts.calendar().url,
        icon: 'material-symbols-light:insert-text',
        children: [
            {
                title: 'Calendar',
                href: posts.calendar().url,
                icon: 'material-symbols-light:add-circle',
            },
            {
                title: 'Manage Posts',
                href: posts.index().url,
                icon: 'material-symbols-light:insert-text',
            },
            {
                title: 'Add New Post',
                href: posts.create().url,
                icon: 'material-symbols-light:add-circle',
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
            {
                title: 'Comments',
                href: '/admin/comments',
                icon: 'material-symbols-light:comment',
            },
        ],
    },
    {
        title: 'Menus',
        href: '/admin/menus',
        icon: 'material-symbols-light:menu',
    },
    {
        title: 'Media',
        href: '/admin/media',
        icon: 'material-symbols-light:perm-media',
    },
    {
        title: 'Social Media',
        href: '/admin/social-media/accounts',
        icon: 'material-symbols-light:share',
        children: [
            {
                title: 'Analytics',
                href: '/admin/social-media/analytics',
                icon: 'material-symbols-light:analytics',
            },
            {
                title: 'Posts',
                href: '/admin/social-media/posts',
                icon: 'material-symbols-light:post',
            },
            {
                title: 'Accounts',
                href: '/admin/social-media/accounts',
                icon: 'material-symbols-light:link',
            },
        ],
    },
    {
        title: 'Appearance',
        href: '/admin/themes',
        icon: 'material-symbols-light:palette',
        children: [
            {
                title: 'Themes',
                href: '/admin/themes',
                icon: 'material-symbols-light:palette',
            },
            {
                title: 'Theme Settings',
                href: '/admin/settings/theme',
                icon: 'material-symbols-light:tune',
            },
        ],
    },
    {
        title: 'Plugins',
        href: '/admin/plugins',
        icon: 'typcn:puzzle',
    },
    {
        title: 'Settings',
        icon: 'material-symbols-light:settings',
        href: '/admin/settings/site',
        children: [
            {
                title: 'Site Settings',
                href: '/admin/settings/site',
                icon: 'material-symbols-light:web',
            },
            {
                title: 'Content Storage',
                href: '/admin/settings/content-storage',
                icon: 'material-symbols-light:storage',
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
        href: 'https://github.com/jkavuncuoglu/contenta',
        icon: 'material-symbols-light:folder',
    },
    {
        title: 'Documentation',
        href: 'https://github.com/jkavuncuoglu/contenta/wiki',
        icon: 'material-symbols-light:bookOpen',
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader class="border-b pb-2">
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

        <div class="text-center w-full text-xs border-t border-t-neutral-800 py-2">{{props.app.name}} v{{props.app.version}}</div>
    </Sidebar>
    <slot />
</template>
`
