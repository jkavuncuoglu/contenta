<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';

interface FooterLink {
    id: number;
    title: string;
    url: string;
    target?: string;
}

interface FooterSection {
    title: string;
    links: FooterLink[];
}

interface SocialLink {
    platform: string;
    url: string;
    icon: string;
}

interface Props {
    footerSections?: FooterSection[];
    socialLinks?: SocialLink[];
    siteName?: string;
    tagline?: string;
    logo?: string;
    copyrightText?: string;
}

const props = withDefaults(defineProps<Props>(), {
    footerSections: () => [],
    socialLinks: () => [],
    siteName: 'Contenta CMS',
    tagline: 'Professional content management made simple',
    copyrightText: undefined,
});

const currentYear = new Date().getFullYear();
const copyright =
    props.copyrightText ||
    `© ${currentYear} ${props.siteName}. All rights reserved.`;

const defaultSocialLinks = [
    { platform: 'Twitter', url: '#', icon: 'ph:twitter-logo' },
    { platform: 'Facebook', url: '#', icon: 'ph:facebook-logo' },
    { platform: 'LinkedIn', url: '#', icon: 'ph:linkedin-logo' },
    { platform: 'GitHub', url: '#', icon: 'ph:github-logo' },
];

const socialLinksToDisplay =
    props.socialLinks.length > 0 ? props.socialLinks : defaultSocialLinks;
</script>

<template>
    <footer
        class="border-t border-neutral-200 bg-neutral-50 transition-colors dark:border-neutral-800 dark:bg-neutral-900"
    >
        <div class="container mx-auto px-4 py-12 sm:px-6 md:py-16 lg:px-8">
            <!-- Main Footer Content -->
            <div
                class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-12 lg:gap-12"
            >
                <!-- Brand Section -->
                <div class="lg:col-span-4">
                    <div class="mb-4 flex items-center space-x-3">
                        <div v-if="logo" class="flex-shrink-0">
                            <img
                                :src="logo"
                                :alt="siteName"
                                class="h-10 w-auto"
                            />
                        </div>
                        <div
                            v-else
                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-purple-600"
                        >
                            <span class="text-xl font-bold text-white">{{
                                siteName.charAt(0)
                            }}</span>
                        </div>
                        <span
                            class="text-2xl font-bold text-neutral-900 dark:text-white"
                        >
                            {{ siteName }}
                        </span>
                    </div>
                    <p class="mb-6 max-w-md text-neutral-600 dark:text-neutral-400" v-html="tagline" />

                    <!-- Social Links -->
                    <div class="flex space-x-4">
                        <a
                            v-for="social in socialLinksToDisplay"
                            :key="social.platform"
                            :href="social.url"
                            :aria-label="social.platform"
                            class="rounded-lg p-2 text-neutral-600 transition-all duration-200 hover:bg-neutral-100 hover:text-blue-600 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-blue-400"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <Icon :icon="social.icon" class="h-5 w-5" />
                        </a>
                    </div>
                </div>

                <!-- Footer Navigation Sections -->
                <div
                    v-if="footerSections.length > 0"
                    class="grid grid-cols-2 gap-8 md:grid-cols-3 lg:col-span-8"
                >
                    <div
                        v-for="(section, index) in footerSections"
                        :key="index"
                    >
                        <h3
                            class="mb-4 text-sm font-semibold tracking-wider text-neutral-900 uppercase dark:text-white"
                        >
                            {{ section.title }}
                        </h3>
                        <ul class="space-y-3">
                            <li v-for="link in section.links" :key="link.id">
                                <Link
                                    :href="link.url"
                                    :target="link.target || '_self'"
                                    class="text-neutral-600 transition-colors hover:text-blue-600 dark:text-neutral-400 dark:hover:text-blue-400"
                                >
                                    {{ link.title }}
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Default Footer Links if no sections provided -->
                <div
                    v-else
                    class="grid grid-cols-2 gap-8 md:grid-cols-3 lg:col-span-8"
                >
                    <!-- Company -->
                    <div>
                        <h3
                            class="mb-4 text-sm font-semibold tracking-wider text-neutral-900 uppercase dark:text-white"
                        >
                            Company
                        </h3>
                        <ul class="space-y-3">
                            <li>
                                <Link
                                    href="/about"
                                    class="text-neutral-600 transition-colors hover:text-blue-600 dark:text-neutral-400 dark:hover:text-blue-400"
                                >
                                    About Us
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/blog"
                                    class="text-neutral-600 transition-colors hover:text-blue-600 dark:text-neutral-400 dark:hover:text-blue-400"
                                >
                                    Blog
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/contact"
                                    class="text-neutral-600 transition-colors hover:text-blue-600 dark:text-neutral-400 dark:hover:text-blue-400"
                                >
                                    Contact
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Resources -->
                    <div>
                        <h3
                            class="mb-4 text-sm font-semibold tracking-wider text-neutral-900 uppercase dark:text-white"
                        >
                            Resources
                        </h3>
                        <ul class="space-y-3">
                            <li>
                                <Link
                                    href="/docs"
                                    class="text-neutral-600 transition-colors hover:text-blue-600 dark:text-neutral-400 dark:hover:text-blue-400"
                                >
                                    Documentation
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/help"
                                    class="text-neutral-600 transition-colors hover:text-blue-600 dark:text-neutral-400 dark:hover:text-blue-400"
                                >
                                    Help Center
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/support"
                                    class="text-neutral-600 transition-colors hover:text-blue-600 dark:text-neutral-400 dark:hover:text-blue-400"
                                >
                                    Support
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div>
                        <h3
                            class="mb-4 text-sm font-semibold tracking-wider text-neutral-900 uppercase dark:text-white"
                        >
                            Legal
                        </h3>
                        <ul class="space-y-3">
                            <li>
                                <Link
                                    href="/privacy"
                                    class="text-neutral-600 transition-colors hover:text-blue-600 dark:text-neutral-400 dark:hover:text-blue-400"
                                >
                                    Privacy Policy
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/terms"
                                    class="text-neutral-600 transition-colors hover:text-blue-600 dark:text-neutral-400 dark:hover:text-blue-400"
                                >
                                    Terms of Service
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/cookies"
                                    class="text-neutral-600 transition-colors hover:text-blue-600 dark:text-neutral-400 dark:hover:text-blue-400"
                                >
                                    Cookie Policy
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div
                class="mt-12 border-t border-neutral-200 pt-8 dark:border-neutral-800"
            >
                <div
                    class="flex flex-col items-center justify-between space-y-4 md:flex-row md:space-y-0"
                >
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        {{ copyright }}
                    </p>
                    <div
                        class="flex items-center space-x-6 text-sm text-neutral-600 dark:text-neutral-400"
                    >
                        <Link
                            href="/privacy"
                            class="transition-colors hover:text-blue-600 dark:hover:text-blue-400"
                        >
                            Privacy
                        </Link>
                        <Link
                            href="/terms"
                            class="transition-colors hover:text-blue-600 dark:hover:text-blue-400"
                        >
                            Terms
                        </Link>
                        <Link
                            href="/cookies"
                            class="transition-colors hover:text-blue-600 dark:hover:text-blue-400"
                        >
                            Cookies
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>
