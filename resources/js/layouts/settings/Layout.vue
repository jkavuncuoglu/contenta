<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { toUrl, urlIsActive } from '@/lib/utils';
import settings from '@/routes/user/settings';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Settings',
        href: settings.profile.edit(),
    },
    {
        title: 'Security',
        href: settings.security.twoFactor.show(),
    },
    {
        title: 'Notifications',
        href: '#',
    },
    {
        title: 'API Tokens',
        href: settings.apiTokens.index(),
    },
];

const page = usePage();
// Try a couple of common shapes so this works with different backends:
const isAdmin = (() => {
    const user = page.props.auth?.user as any;
    if (!user) return false;
    console.log(user)
    // Common boolean attribute
    if (typeof user.is_admin === 'boolean') return user.is_admin;
    // camelCase / method-like flag
    if (typeof user.isAdmin === 'boolean') return user.isAdmin;
    // Roles array (spatie) - check for admin role
    if (Array.isArray(user.roles)) {
        return user.roles.some((r: any) => (r?.name || r) === 'admin' || (r?.name || r) === 'super-admin');
    }
    // As a fallback, check for a simple role string
    if (typeof user.role === 'string') {
        return ['admin', 'super-admin'].includes(user.role);
    }
    return false;
})();

const canUseApiTokens = (() => {
    const user = page.props.auth?.user as any;
    if (!user) return false;

    // Check if user has the 'can' attribute (common pattern)
    if (typeof user.can === 'object' && user.can !== null) {
        return user.can['api-tokens.use'] === true;
    }

    // Fallback: super-admin and admin always have access
    return isAdmin;
})();

const filteredSidebarNavItems = sidebarNavItems.filter(item => {
    // Filter out API Tokens if user doesn't have permission
    if (item.title === 'API Tokens') {
        return canUseApiTokens;
    }
    return true;
});

const currentPath = typeof window !== undefined ? window.location.pathname : '';
</script>

<template>
    <div class="px-4 py-6">
        <Heading title="Settings" description="Manage your profile and account settings" />

        <div class="flex flex-col lg:flex-row lg:space-x-12 mt-8">
            <aside class="w-full max-w-xl lg:w-48 bg-neutral-900/30">
                <nav class="flex flex-col space-y-1 space-x-0  ">
                    <Button
                        v-for="item in filteredSidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': urlIsActive(item.href, currentPath) }]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>

                <!-- Footer links: Website and Admin (admin shown conditionally) -->
                <div class="mt-6 border-t pt-4 w-full max-w-xl lg:w-48 absolute bottom-6">
                    <nav class="flex flex-col space-y-2 px-1">
                        <a
                            href="/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-sm text-muted-foreground hover:text-foreground"
                        >
                            Website
                        </a>

                        <a
                            v-if="isAdmin"
                            href="/admin"
                            class="text-sm text-muted-foreground hover:text-foreground"
                        >
                            Admin
                        </a>
                    </nav>
                </div>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
