<template>
  <div class="min-h-screen bg-white dark:bg-gray-900 flex flex-col">
    <!-- Header -->
    <SiteHeader
      :navigation="navigation.primary"
      :site-name="siteName"
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
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SiteHeader from '@/components/SiteHeader.vue';
import SiteFooter from '@/components/SiteFooter.vue';

const page = usePage();

const navigation = computed(() => page.props.navigation || { primary: [], footer: [] });
const siteName = computed(() => page.props.name || 'Contenta');
const tagline = computed(() => 'Professional content management made simple');
</script>
