<template>
    <div class="flex min-h-screen flex-col bg-white dark:bg-neutral-900">
        <!-- Header -->
        <SiteHeader
            :navigation="navigation.primary"
            :site-name="siteName"
            :logo="`images/contenta-logo-icon.svg`"
        />

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <SiteFooter
            :footer-sections="navigation.footer"
            :site-name="siteName"
            :tagline="tagline"
            :logo="`images/contenta-logo-icon.svg`"
        />
    </div>
</template>

<script setup lang="ts">
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const navigation = computed(
    () => page.props.navigation || { primary: [], footer: [] },
);
const siteName = computed(() => page.props.name || 'Contenta CMS');
const tagline = computed(() => 'Professional content management made simple. <br/>Build with ❤️ by <a href=\'https://kavuncuoglu.com\' class="font-monospace font-medium">Jeremy Kavuncuoglu</a> and <br />the community.');
</script>
