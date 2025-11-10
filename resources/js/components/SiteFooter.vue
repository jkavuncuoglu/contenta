<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

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
  siteName: 'Contenta',
  tagline: 'Professional content management made simple',
  copyrightText: undefined,
});

const currentYear = new Date().getFullYear();
const copyright = props.copyrightText || `© ${currentYear} ${props.siteName}. All rights reserved.`;

const defaultSocialLinks = [
  { platform: 'Twitter', url: '#', icon: 'ph:twitter-logo' },
  { platform: 'Facebook', url: '#', icon: 'ph:facebook-logo' },
  { platform: 'LinkedIn', url: '#', icon: 'ph:linkedin-logo' },
  { platform: 'GitHub', url: '#', icon: 'ph:github-logo' },
];

const socialLinksToDisplay = props.socialLinks.length > 0 ? props.socialLinks : defaultSocialLinks;
</script>

<template>
  <footer class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 transition-colors">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
      <!-- Main Footer Content -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-12">
        <!-- Brand Section -->
        <div class="lg:col-span-4">
          <div class="flex items-center space-x-3 mb-4">
            <div v-if="logo" class="flex-shrink-0">
              <img :src="logo" :alt="siteName" class="h-10 w-auto" />
            </div>
            <div v-else class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-xl">{{ siteName.charAt(0) }}</span>
            </div>
            <span class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ siteName }}
            </span>
          </div>
          <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md">
            {{ tagline }}
          </p>

          <!-- Social Links -->
          <div class="flex space-x-4">
            <a
              v-for="social in socialLinksToDisplay"
              :key="social.platform"
              :href="social.url"
              :aria-label="social.platform"
              class="p-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-all duration-200"
              target="_blank"
              rel="noopener noreferrer"
            >
              <Icon :icon="social.icon" class="w-5 h-5" />
            </a>
          </div>
        </div>

        <!-- Footer Navigation Sections -->
        <div v-if="footerSections.length > 0" class="lg:col-span-8 grid grid-cols-2 md:grid-cols-3 gap-8">
          <div v-for="(section, index) in footerSections" :key="index">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
              {{ section.title }}
            </h3>
            <ul class="space-y-3">
              <li v-for="link in section.links" :key="link.id">
                <Link
                  :href="link.url"
                  :target="link.target || '_self'"
                  class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                >
                  {{ link.title }}
                </Link>
              </li>
            </ul>
          </div>
        </div>

        <!-- Default Footer Links if no sections provided -->
        <div v-else class="lg:col-span-8 grid grid-cols-2 md:grid-cols-3 gap-8">
          <!-- Company -->
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
              Company
            </h3>
            <ul class="space-y-3">
              <li>
                <Link href="/about" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  About Us
                </Link>
              </li>
              <li>
                <Link href="/blog" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Blog
                </Link>
              </li>
              <li>
                <Link href="/contact" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Contact
                </Link>
              </li>
            </ul>
          </div>

          <!-- Resources -->
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
              Resources
            </h3>
            <ul class="space-y-3">
              <li>
                <Link href="/docs" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Documentation
                </Link>
              </li>
              <li>
                <Link href="/help" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Help Center
                </Link>
              </li>
              <li>
                <Link href="/support" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Support
                </Link>
              </li>
            </ul>
          </div>

          <!-- Legal -->
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
              Legal
            </h3>
            <ul class="space-y-3">
              <li>
                <Link href="/privacy" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Privacy Policy
                </Link>
              </li>
              <li>
                <Link href="/terms" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Terms of Service
                </Link>
              </li>
              <li>
                <Link href="/cookies" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Cookie Policy
                </Link>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-800">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
          <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ copyright }}
          </p>
          <div class="flex items-center space-x-6 text-sm text-gray-600 dark:text-gray-400">
            <Link href="/privacy" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
              Privacy
            </Link>
            <Link href="/terms" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
              Terms
            </Link>
            <Link href="/cookies" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
              Cookies
            </Link>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>
